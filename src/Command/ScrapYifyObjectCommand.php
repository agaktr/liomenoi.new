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

class ScrapYifyObjectCommand extends Command
{
    protected static $defaultName = 'ScrapYifyObject';
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
        $io->title('Starting to scrap YIFY');

        $objects = $this->em->getRepository(YifyObject::class)->findAll();



        foreach ($objects as $object) {

            $this->urls[$object->getId()] = 'https://yts.do'.$object->getSlug();
        }

        $this->scrapper->setUrls(array_slice($this->urls,0,1,true));

        $this->scrapper->initObjects();

//        foreach ($this->scrapper->getScrappedContent() as $scrap) {
//
//            $object = new YifyObject();
//            $object->setTitle($scrap['title']);
//            $object->setYear($scrap['year']);
//            $object->setSlug($scrap['slug']);
//
//            $this->em->persist($object);
//        }
//
//        $this->em->flush();

        $content = sprintf('ScrapYIFY: %s objects added. DONE Time: %s', count($this->scrapper->getScrappedContent()),json_encode($this->scrapper->getPerformance()));

        $io->success($content);

        return Command::SUCCESS;
    }
}

