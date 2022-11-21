<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Movie;
use App\Entity\YifyObject;
use App\Service\ScrapperService;
use App\Service\TMDBService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScrapTMDBCommand extends Command
{
    protected static $defaultName = 'ScrapTMDB';
    protected static $defaultDescription = 'This command scraps objects from YIFY so we can get the torrents.';

    private EntityManagerInterface $em;
    private TMDBService $scrapper;


    public function __construct(EntityManagerInterface $entityManager,TMDBService $tmdbService)
    {

        $this->em = $entityManager;
        $this->scrapper = $tmdbService;

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

        $io->title('Starting to scrap Objects');

        $objects = $this->em->getRepository(Movie::class)->findBy([],[ 'id' => 'ASC'], 2,0);

        var_dump($objects);

        return Command::SUCCESS;
    }
}

