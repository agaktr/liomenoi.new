<?php

namespace App\Command;

use App\Entity\Provider;
use App\Entity\Scrap;
use App\Entity\YifyObject;
use App\Service\ScrapperService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProviderCommand extends Command
{
    protected static $defaultName = 'Provider';
    protected static $defaultDescription = 'This command scraps objects from YIFY so we can get the torrents.';

    private array $urls = [];

    private EntityManagerInterface $em;
    private ScrapperService $scrapper;


    public function __construct(EntityManagerInterface $entityManager,ScrapperService $scrapperService)
    {

        $this->em = $entityManager;
//        $this->scrapper = $scrapperService;

        parent::__construct();
    }

    protected function configure(): void
    {


        $this
            ->setHelp('The command is run via a cron job once in a while.')
//            ->addArgument('reportId', InputArgument::OPTIONAL, 'Add reportId')
            ->addOption('page', null, InputOption::VALUE_REQUIRED, 'the page to start from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting Provider Scrapping');

        //Get Least updated provider
        $provider = $this->em->getRepository(Provider::class)->findOneBy([],['updated' => 'ASC']);

        $io->info('Provider: '.$provider->getName());

        //init variables
        $currentPage = $input->getOption('page') ? $input->getOption('page') : 1;
        $pagesNo = 5;
        $hasMore = true;
        $doing = 'Movie';

        $objectsLocal = $this->em->getRepository(Scrap::class)->findAll();

        $objectsLocalArray = [];
        foreach($objectsLocal as $object){
            $objectKey = $object->getName().'-'.$object->getProvider()->getId();
            $objectsLocalArray[$objectKey] = $object;
        }

        //While we still add and not update only
        while ($hasMore) {

            $io->text('Doing page '.$currentPage.' to '.($currentPage + ($pagesNo - 1)));

            //Setup pages to scrap
            $this->scrapper = new ScrapperService();
            $this->urls = [];

            for ($i = $currentPage; $i < $currentPage + $pagesNo; $i++) {

                $this->urls[] =
                    $provider->getDomain().
                    $provider->{'get'.$doing.'Path'}().
                    $provider->getPageQueryString().
                    $i;
            }
            $currentPage = $currentPage + $pagesNo;

            //Init scrapper
            $this->scrapper->setUrls($this->urls);
            $this->scrapper->setProvider($provider);
            $this->scrapper->setDoing($doing);

            //Scrap
            $this->scrapper->getContent();
            $this->scrapper->getScraps();

            //Save scraps
            $added = $updated = 0;
            foreach ($this->scrapper->getScrappedContent() as $scrap) {

                $objectKey = $scrap['title'].'-'.$provider->getId();
                if(!isset($objectsLocalArray[$objectKey])){
                    $object = new Scrap();
                    $this->em->persist($object);
                    $objectsLocalArray[$objectKey] = $object;
                    ++$added;
                }else{
                    ++$updated;
                }

                $object->setProvider($provider);
                $object->setName($scrap['title']);
                $object->setYear($scrap['year']);
                $object->setSlug($scrap['slug']);
                $object->setCreated(new DateTime());
                $object->setUpdated(new DateTime());
            }

            //flush each scrap
            $this->em->flush();

            $content = sprintf('Provider: %s objects added. %s objects updated. DONE Time: %s', $added,$updated,json_encode($this->scrapper->getPerformance()));

            $io->success($content);

            //If we did not add anything we are done
            if ($added == 0) {
                $hasMore = false;
            }
        }

        //Update provider
        $provider->setUpdated(new DateTime());
        $this->em->flush();

        return Command::SUCCESS;
    }
}

