<?php

namespace App\Command;

use App\Entity\Provider;
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
        $this->scrapper = $scrapperService;

        parent::__construct();
    }

    protected function configure(): void
    {


        $this
            ->setHelp('The command is run via a cron job once in a while.')
//            ->addArgument('reportId', InputArgument::OPTIONAL, 'Add reportId')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting Provider Scrapping');

        //Get Least updated provider
        $provider = $this->em->getRepository(Provider::class)->findOneBy([],['updated' => 'ASC']);

        $io->info('Provider: '.$provider->getName());

        $currentPage = 1;
        $pagesNo = 1;
        $hasMore = true;
        $doing = 'Movie';

        while ($hasMore) {

            $io->text('Doing page '.$currentPage.' to '.($currentPage + $pagesNo));

            for ($i = $currentPage; $i < $currentPage + $pagesNo; $i++) {

                $this->urls[] =
                    $provider->getDomain().
                    $provider->{'get'.$doing.'Path'}().
                    $provider->getPageQueryString().
                    $i;
            }

            var_dump($this->urls);
//            $currentPage = $currentPage + 5;
//
//            $this->scrapper->setUrls($this->urls);
//
//            $this->scrapper->initSlugs();
//
//            $added = $updated = 0;
//
//            foreach ($this->scrapper->getScrappedContent() as $scrap) {
//
//                $object = $this->em->getRepository(YifyObject::class)->findOneBy(['slug' => $scrap['slug']]);
//
//
//                if (!$object) {
//                    ++$added;
//                    $object = new YifyObject();
//                    $this->em->persist($object);
//                }else{
//                    ++$updated;
//                }
//
//                $object->setTitle($scrap['title']);
//                $object->setYear($scrap['year']);
//                $object->setSlug($scrap['slug']);
//                $object->setFetched(false);
//            }
//
//            $this->em->flush();
//
//            $content = sprintf('ScrapYIFY: %s objects added. %s objects updated. DONE Time: %s', $added,$updated,json_encode($this->scrapper->getPerformance()));
//
//            $io->success($content);
//
//            unset($this->urls);

            $hasMore = false;
        }

        return Command::SUCCESS;
    }
}

