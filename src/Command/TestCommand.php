<?php

namespace App\Command;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Magnet;
use App\Entity\Movie;
use App\Entity\Provider;
use App\Entity\Scrap;
use App\Entity\YifyObject;
use App\Service\ScrapperService;
use App\Service\TMDBService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tmdb\Repository\MovieRepository;

class TestCommand extends Command
{
    protected static $defaultName = 'Test';
    protected static $defaultDescription = 'This command scraps objects from YIFY so we can get the torrents.';

    private array $urls = [];
    private array $objectsMap = [];
    private array $objectsLocalArray = [];
    private array $magnetsLocalArray = [];
    private array $genresLocalArray = [];
    private array $actorsLocalArray = [];

    private EntityManagerInterface $em;
    private ScrapperService $scrapper;
    private TMDBService $tmdbService;


    public function __construct(EntityManagerInterface $entityManager,ScrapperService $scrapperService,TMDBService $tmdbService)
    {

        $this->em = $entityManager;
        $this->scrapper = $scrapperService;
        $this->tmdbService = $tmdbService;

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



        var_dump(file_get_contents('https://ytsmx.xyz/wp-mov/Gringo%20(2018)%20[BluRay]%20[720p].torrent'));

        return Command::SUCCESS;
    }
}

