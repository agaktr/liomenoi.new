<?php

namespace App\Command;

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

        $io->title('Starting to scrap YIFY');

        $currentPage = 2240;

        while ($currentPage < 2500) {

            $io->title('Doing page '.$currentPage.' to '.($currentPage + 5));

            for ($i = $currentPage; $i < $currentPage + 5; $i++) {

                $this->urls[] = 'https://yts.do/browse-movies?page='.$i;
            }
            $currentPage = $currentPage + 5;

            $this->scrapper->setUrls($this->urls);

            $this->scrapper->initSlugs();

            $added = $updated = 0;

            foreach ($this->scrapper->getScrappedContent() as $scrap) {

                $object = $this->em->getRepository(YifyObject::class)->findOneBy(['slug' => $scrap['slug']]);


                if (!$object) {
                    ++$added;
                    $object = new YifyObject();
                    $this->em->persist($object);
                }else{
                    ++$updated;
                }

                $object->setTitle($scrap['title']);
                $object->setYear($scrap['year']);
                $object->setSlug($scrap['slug']);
                $object->setFetched(false);
            }

            $this->em->flush();

            $content = sprintf('ScrapYIFY: %s objects added. %s objects updated. DONE Time: %s', $added,$updated,json_encode($this->scrapper->getPerformance()));

            $io->success($content);

            unset($this->urls);
        }

        return Command::SUCCESS;
    }
}

