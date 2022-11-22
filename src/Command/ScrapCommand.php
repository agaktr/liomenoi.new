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

class ScrapCommand extends Command
{
    protected static $defaultName = 'Scrap';
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

        ini_set('memory_limit', '2048M');

        $io = new SymfonyStyle($input, $output);

        //get genres
        $genresLocal = $this->em->getRepository(Category::class)->findAll();
        $this->genresLocalArray = [];
        foreach($genresLocal as $genre){
            $this->genresLocalArray[$genre->getTmdbId()] = $genre;
        }
        unset($genresLocal);

        //get actors
        $actorsLocal = $this->em->getRepository(Actor::class)->findAll();
        $this->actorsLocalArray = [];
        foreach($actorsLocal as $actor){
            $this->actorsLocalArray[$actor->getTmdbId()] = $actor;
        }
        unset($actorsLocal);

        //Get providers
        $providers = $this->em->getRepository(Provider::class)->findAll();
        foreach ($providers as $provider) {

            $io->title('Provider: ' . $provider->getName());
            $io->title('Starting Movie scrapping');

            //init variables
            $hasMore = true;
            $pagesNo = 1;
            $doing = 'Movie';

            switch ($doing) {
                case 'Movie':
                    //get local scrap
                    $objectsLocal = $this->em->getRepository(Movie::class)->findAll();
                    $this->objectsLocalArray = [];
                    foreach($objectsLocal as $object){
                        $objectKey = $object->getMatchName().'-'.$object->getYear();
                        $this->objectsLocalArray[$objectKey] = $object;

                    }
                    unset($objectsLocal);

                    //get local magnets
                    $objectsLocal = $this->em->getRepository(Magnet::class)->findAll();
                    $this->magnetsLocalArray = [];
                    foreach($objectsLocal as $object){
                        $objectKey = $object->getMagnet();
                        $this->magnetsLocalArray[$objectKey] = $object;
                    }
                    unset($objectsLocal);
                    break;
                case 'Serie':
                    die();
                    break;
            }

            while ($hasMore) {

                $objects = $this->em->getRepository(Scrap::class)->findBy(['valid' => null , 'type' => $doing,'provider'=>$provider] , ['id' => 'ASC'] , $pagesNo , 0);

                $this->objectsMap = [];
                $this->urls = [];
                foreach ($objects as $object) {

//                if (
//                    strpos($object->getSlug(), '%') !== false
//                ) {
//                    $this->em->remove($object);
//                    $this->em->flush();
//                    $io->title('%% deleting '.$object->getId());
//                    continue;
//                }
//                if (
//                    strpos($object->getSlug(), '_') !== false
//                ) {
//                    $this->em->remove($object);
//                    $this->em->flush();
//                    $io->title('__ deleting '.$object->getId());
//                    continue;
//                }

                    $this->objectsMap[ $object->getId() ] = $object;
                    $this->urls[ $object->getId() ] = substr($object->getProvider()->getDomain() , 0 , -1) . $object->getSlug();
                }

                $io->text('Doing ids '.json_encode(array_keys($this->objectsMap)));

                //Init scrapper
                $this->scrapper->setUrls($this->urls);
                $this->scrapper->setProvider($provider);
                $this->scrapper->setDoing($doing);

                //Scrap
                $this->scrapper->getContent();
                $this->scrapper->getScraps();



//                var_dump($this->scrapper->getScrappedContent());
//                die();
//
//                try {
//                    $this->scrapper->initObjects();
//                } catch (ErrorException $e) {
//
//                    $io->title('deleting ' . $e->getMessage());
//                    unset($this->objectsMap[ $e->getMessage() ]);
//                    unset($this->urls[ $e->getMessage() ]);
//                    $this->em->remove($this->objectsMap[ $e->getMessage() ]);
//                    $this->em->flush();
//                    continue;
//                } catch (\Exception $e) {
//                    $io->title('retry ' . $currentPage);
//                    continue;
//                }


                $results = $this->scrapper->getScrappedContent();
                foreach ($results as $id => $content) {

                    $results[ $id ][ 'data' ] = $this->objectsMap[ $id ];
                }

//                $added = $updated = 0;
//                $addedMagnet = $updatedMagnet = 0;

                foreach ($results as $objectId => $objectData) {

                    switch ($doing) {
                        case 'Movie':
                            $this->handleMovie($objectId,$objectData,$io);
                            break;
                        case 'Serie':
                            die();
                            break;
                    }
                }

                $this->em->flush();

                $content = sprintf('DONE Time: %s' , json_encode($this->scrapper->getPerformance()));

                $io->success($content);

                if (count($this->objectsMap) < $pagesNo) {
                    $hasMore = false;
                }
            }
        }

        return Command::SUCCESS;
    }

    private function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }


    private function handleMovie(int $objectId, array $movieData,$io)
    {

        $start = microtime(true);

        /** @var Movie $movie */
        $objectKey =$movieData[ 'data' ]->getName().'-'.$movieData[ 'data' ]->getYear();

        if(!isset($this->objectsLocalArray[$objectKey])){
            $io->note('Creating new Object');
            $movie = new Movie();
            $this->em->persist($movie);
            $this->objectsLocalArray[$objectKey] = $movie;
        }else{
            $io->info('Existing Object');
            $movie = $this->objectsLocalArray[$objectKey];
        }

        //Scrap stuff
        $movie->addScrap($movieData[ 'data' ]);
        $movie->setMatchName($movieData[ 'data' ]->getName());

        //Imdb stuff
        $movie->setImdb($movieData[ 'imdb' ]);

//        if ( !isset($movieData[ 'magnet' ]) ) {
//            $io->title('magnet__deleting ' . $movie->getId());
//            unset($this->objectsMap[ $movie->getId() ]);
//            unset($this->urls[ $movie->getId() ]);
//            $this->em->remove($movie);
//            $this->em->flush();
//            continue;
//        }


        //Magnet stuff
        foreach ($movieData[ 'magnet' ] as $magnetLink) {

            /** @var Magnet $magnet */
            $objectKey = $magnetLink[ 'magnet' ];
            if(!isset($this->magnetsLocalArray[$objectKey])){
                $magnet = new Magnet();
                $this->em->persist($magnet);
                $this->magnetsLocalArray[$objectKey] = $magnet;
            }else{
                $magnet = $this->magnetsLocalArray[$objectKey];
            }

            $magnet->setType($magnetLink[ 'type' ]);
            $magnet->setQuality($magnetLink[ 'quality' ]);
            $magnet->setSize($magnetLink[ 'size' ]);
            $magnet->setMagnet($magnetLink[ 'magnet' ]);
            $magnet->setMovie($movie);
        }

        $this->objectsMap[ $objectId ]->setValid(true);

        /**
         * START TMDB STUFF
         */
        $io->title(': Doing '.$movie->getMatchName());

        //scrap imdb id from imdb url $movie->getImdb
        $imdbId = preg_filter('/^.*\/(tt\d+).*$/','$1',$movie->getImdb());

        //find movie from tmdb based on imdb id
        $tmdbMovieRes = $this->tmdbService->client->getFindApi()->findBy($imdbId,['external_source' => 'imdb_id']);

//        if (empty($tmdbMovieRes['movie_results'])){
//            $movie->setFetched(false);
//            $this->em->flush();
//
//            $io->error('No movie found for '.$movie->getTitle());
//            continue;
//        }
        $tmdbMovie = $tmdbMovieRes["movie_results"][0];

        //get en/gr version of movie
        /** @var \Tmdb\Model\Movie $modelMovie */
        /** @var \Tmdb\Model\Movie $modelMovieGr */
        $repository = new MovieRepository($this->tmdbService->client);
        $modelMovie = $repository->load($tmdbMovie["id"]);
        $modelMovieGr = $repository->load($tmdbMovie["id"],['language' => 'el-GR']);
        //set tmdb id
        $movie->setTmdbId($tmdbMovie["id"]);

        //set title
        $movie->setTitle($modelMovieGr->getTitle());

        //set original title
        $movie->setOriginalTitle($modelMovie->getTitle());

        //set overview
        $movie->setOverview($modelMovieGr->getOverview());
        if (empty($movie->getOverview()))
            $movie->setOverview($modelMovie->getOverview());

        //set poster
        $movie->setPoster($modelMovie->getPosterPath());

        //set backdrop
        $movie->setBackdrop($modelMovie->getBackdropPath());

        //set release date
        $movie->setReleaseDate($modelMovie->getReleaseDate());

        //set year
        $movie->setYear($modelMovie->getReleaseDate()->format('Y'));

        //set runtime
        $movie->setRuntime($modelMovie->getRuntime());

        //set genres
        foreach($modelMovie->getGenres() as $genre){
            if(isset($this->genresLocalArray[$genre->getId()])){
                $movie->addCategory($this->genresLocalArray[$genre->getId()]);
            }
        }

        //set actors
        foreach($modelMovie->getCredits()->getCast() as $actorModel){
            if(!isset($this->actorsLocalArray[$actorModel->getId()])){

                $actor = new Actor();
                $this->em->persist($actor);
                $actor->setName($actorModel->getName());
                $actor->setTmdbId($actorModel->getId());
                $actor->setPoster('');
                if (!empty($actorModel->getProfilePath()))
                    $actor->setPoster($actorModel->getProfilePath());
                $this->actorsLocalArray[$actorModel->getId()] = $actor;

                $io->text('Added new actor '.$actorModel->getName());
            }else{
                $io->text('Found actor '.$actorModel->getName());
            }

            $movie->addActor($this->actorsLocalArray[$actorModel->getId()]);
        }

        //set slug
        $movie->setSlug($this->slugify($movie->getOriginalTitle().'-'.$movie->getYear()));

        $movie->setFetched(true);

        $this->em->flush();

        $perf = $this->scrapper->getPerformance();
        $perf['handle'] = microtime(true) - $start;
        $this->scrapper->setPerformance($perf);
    }
}

