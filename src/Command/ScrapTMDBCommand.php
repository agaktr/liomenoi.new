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
use Tmdb\Repository\MovieRepository;

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

        $objects = $this->em->getRepository(Movie::class)->findBy([],[ 'id' => 'ASC'], 1,0);

        foreach ($objects as $object){

            //scrap imdb id from imdb url $object->getImdb
            $imdbId = preg_filter('/^.*\/(tt\d+).*$/','$1',$object->getImdb());
            var_dump($imdbId);

            //find movie from tmdb based on imdb id
            $tmdbMovie = $this->scrapper->client->getFindApi()->findBy($imdbId,['external_source' => 'imdb_id'])["movie_results"][0];

            //get gr version
            $repository = new MovieRepository($this->scrapper->client);
            /** @var \Tmdb\Model\Movie $movie */
            $modelMovie = $repository->load($tmdbMovie["id"],['language' => 'el-GR']);
//            $tmdbMovieGr = $this->scrapper->client->getMoviesApi()->getMovie($tmdbMovie["id"],['language' => 'el-GR']);

            var_dump($modelMovie);

//            var_dump($object->getImdb());
        }


        return Command::SUCCESS;
    }
}

