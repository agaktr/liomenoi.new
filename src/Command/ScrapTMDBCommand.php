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

        $genresLocal = $this->em->getRepository(Category::class)->findAll();

        $genresLocalArray = [];
        foreach($genresLocal as $genre){
            $genresLocalArray[$genre->getTmdbId()] = $genre;
        }

        foreach ($objects as $object){

            //scrap imdb id from imdb url $object->getImdb
            $imdbId = preg_filter('/^.*\/(tt\d+).*$/','$1',$object->getImdb());

            //find movie from tmdb based on imdb id
            $tmdbMovie = $this->scrapper->client->getFindApi()->findBy($imdbId,['external_source' => 'imdb_id'])["movie_results"][0];

            //get en/gr version of movie
            /** @var \Tmdb\Model\Movie $modelMovie */
            /** @var \Tmdb\Model\Movie $modelMovieGr */
            $repository = new MovieRepository($this->scrapper->client);
            $modelMovie = $repository->load($tmdbMovie["id"]);
            $modelMovieGr = $repository->load($tmdbMovie["id"],['language' => 'el-GR']);

            //set tmdb id
            $object->setTmdbId($tmdbMovie["id"]);

            //set title
            $object->setTitle($modelMovieGr->getTitle());

            //set original title
            $object->setOriginalTitle($modelMovie->getTitle());

            //set overview
            $object->setOverview($modelMovieGr->getOverview());

            //set poster
            $object->setPoster($modelMovie->getPosterPath());

            //set backdrop
            $object->setBackdrop($modelMovie->getBackdropPath());

            //set release date
            $object->setReleaseDate($modelMovie->getReleaseDate());

            //set runtime
            $object->setRuntime($modelMovie->getRuntime());

            //set genres
            foreach($modelMovie->getGenres() as $genre){
                if(isset($genresLocalArray[$genre->getId()])){
                    $object->addCategory($genresLocalArray[$genre->getId()]);
                }
            }
        }

        $this->em->flush();


        return Command::SUCCESS;
    }
}

