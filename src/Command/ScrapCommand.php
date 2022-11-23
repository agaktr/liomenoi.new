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
    private array $providers = [];
    private bool $fast = true;

    private EntityManagerInterface $em;
    private ScrapperService $scrapper;
    private TMDBService $tmdbService;
    private SymfonyStyle $io;
    private InputInterface $input;
    private OutputInterface $output;


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
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'the page to start from')
            ->addOption('slow', null, InputOption::VALUE_NONE, 'the page to start from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->load($input,$output);

        $io = $this->io;

        foreach ($this->providers as $provider) {

            $io->title('Provider: ' . $provider->getName());
            $io->title('Starting Movie scrapping');

            //init variables
            $hasMore = true;
            $pagesNo = 3;
            $doing = 'Movie';

            switch ($doing) {
                case 'Movie':

                    break;
                case 'Serie':
                    die();
                    break;
            }

            while ($hasMore) {

                $start = microtime(true);

                $objects = $this->em->getRepository(Scrap::class)->findBy(['valid' => null , 'type' => $doing,'provider'=>$provider] , ['id' => 'ASC'] , $pagesNo , 0);

                $this->objectsMap = [];
                $this->urls = [];
                foreach ($objects as $object) {

                    $this->objectsMap[ $object->getId() ] = $object;
                    $this->urls[ $object->getId() ] = substr($object->getProvider()->getDomain() , 0 , -1) . $object->getSlug();
                }

                $io->text('Doing ids '.json_encode(array_keys($this->objectsMap)));

                //Init scrapper
                $this->scrapper->setUrls($this->urls);
                $this->scrapper->setProvider($provider);
                $this->scrapper->setDoing($doing);
                $this->scrapper->setIo($io);

                //Scrap
                $this->scrapper->getContent();
                $this->scrapper->getScraps();

                $results = $this->scrapper->getScrappedContent();

                foreach ($results as $id => $content) {

                    $results[ $id ][ 'data' ] = $this->objectsMap[ $id ];
                }

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

                $perf = $this->scrapper->getPerformance();
                $perf['total'] = microtime(true) - $start;
                $this->scrapper->setPerformance($perf);

                $content = sprintf('DONE Time: %s' , json_encode($this->scrapper->getPerformance()));

                $io->success($content);

                if (count($this->objectsMap) < $pagesNo) {
                    $hasMore = false;
                }
            }
        }

        return Command::SUCCESS;
    }

    private function load(InputInterface $input, OutputInterface $output)
    {

        //set memory to big
        ini_set('memory_limit', '2048M');

        //global vars
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);
        $this->fast = $input->getOption('slow') ? false : true;

        if ($this->fast){
            $this->io->title('Fast mode');

            //load genres
            $this->io->title('Loading genres...');
            $genresLocal = $this->em->getRepository(Category::class)->findAll();
            $this->genresLocalArray = [];
            foreach($genresLocal as $genre){
                $this->genresLocalArray[$genre->getTmdbId()] = $genre;
            }
            unset($genresLocal);

            //load actors
            $this->io->title('Loading actors...');
            $actorsLocal = $this->em->getRepository(Actor::class)->findAll();
            $this->actorsLocalArray = [];
            foreach($actorsLocal as $actor){
                $this->actorsLocalArray[$actor->getTmdbId()] = $actor;
            }
            unset($actorsLocal);

            //load local movies
            $this->io->title('Loading local scrap...');
            $objectsLocal = $this->em->getRepository(Movie::class)->findAll();
            $this->objectsLocalArray = [];
            foreach($objectsLocal as $object){
                $objectKey = $object->getMatchName().'-'.$object->getYear();
                $this->objectsLocalArray[$objectKey] = $object;

            }
            unset($objectsLocal);

            //load local magnets
            $this->io->title('Loading local magnets...');
            $objectsLocal = $this->em->getRepository(Magnet::class)->findAll();
            $this->magnetsLocalArray = [];
            foreach($objectsLocal as $object){
                $objectKey = $object->getMagnet();
                $this->magnetsLocalArray[$objectKey] = $object;
            }
            unset($objectsLocal);
        } else {
            $this->io->title('Slow mode');
        }

        //load providers
        $this->io->title('Loading providers...');
        $providerInput = $input->getOption('provider') ? $input->getOption('provider') : 0;
        if ($providerInput == 0){
            $this->providers = $this->em->getRepository(Provider::class)->findAll();
        }else{
            $this->providers = $this->em->getRepository(Provider::class)->findBy(['id'=>$providerInput]);
        }
    }

    private function handleMovie(int $objectId, array $movieData,$io)
    {

        $start = microtime(true);

        /** @var Movie $movie */
        if ($this->fast){
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
        }else{
            $movie = $this->em->getRepository(Movie::class)->findOneBy(['matchName'=>$movieData[ 'data' ]->getName(),'year'=>$movieData[ 'data' ]->getYear()]);
            if (!$movie){
                $io->note('Creating new Object');
                $movie = new Movie();
                $this->em->persist($movie);
            }else{
                $io->info('Existing Object');
            }
        }

        //Scrap stuff
        $movie->addScrap($movieData[ 'data' ]);
        $movie->setMatchName($movieData[ 'data' ]->getName());

        //general stuff
        $movie->setTitle($movie->getMatchName());
        $movie->setYear($movieData[ 'data' ]->getYear());
        $movie->setSlug('N/A');

        if ( !isset($movieData[ 'magnet' ]) ) {
            $io->error('No Magnets for '.$movie->getMatchName());
            $movie->setFetched(false);
            $movie->setImdb(false);
            $this->objectsMap[ $objectId ]->setValid(false);
            $this->em->flush();
            return;
        }

        //Magnet stuff
        foreach ($movieData[ 'magnet' ] as $magnetLink) {

            /** @var Magnet $magnet */
            if ($this->fast) {
                $objectKey = $magnetLink[ 'magnet' ];
                if ( !isset($this->magnetsLocalArray[ $objectKey ]) ) {
                    $magnet = new Magnet();
                    $this->em->persist($magnet);
                    $this->magnetsLocalArray[ $objectKey ] = $magnet;
                } else {
                    $magnet = $this->magnetsLocalArray[ $objectKey ];
                }
            } else {
                $magnet = $this->em->getRepository(Magnet::class)->findOneBy(['magnet'=>$magnetLink[ 'magnet' ]]);
                if (!$magnet){
                    $magnet = new Magnet();
                    $this->em->persist($magnet);
                }
            }

            $magnet->setType($magnetLink[ 'type' ]);
            $magnet->setQuality($magnetLink[ 'quality' ]);
            $magnet->setSize($magnetLink[ 'size' ]);
            $magnet->setMagnet($magnetLink[ 'magnet' ]);
            $magnet->setMovie($movie);
        }

        /**
         * START IMDB TMDB STUFF
         */
        if (null === $movieData[ 'imdb' ]){
            $io->error('No imdb for '.$movie->getMatchName());
            $io->info('Searching for it.. ');
            //try to find imdb with the Search Api
            $tmdbMovieRes = $this->tmdbService->client->getSearchApi()->searchMovies($movie->getMatchName(),['year'=>$movie->getYear()]);
            $tmdbMovieRes = $this->determineResults($movie,$tmdbMovieRes);
            if (null === $tmdbMovieRes){
                $io->error('No TMDB for '.$movie->getMatchName());
                $movie->setImdb(false);
                $movie->setFetched(false);
                $this->objectsMap[ $objectId ]->setValid(false);
                $this->em->flush();
                return;
            }

            $tmdbMovie = $this->tmdbService->client->getMoviesApi()->getMovie($tmdbMovieRes['id']);
            $movie->setImdb('https://www.imdb.com/title/'.$tmdbMovie['imdb_id']);
        }else{
            $movie->setImdb($movieData[ 'imdb' ]);
        }

        $this->objectsMap[ $objectId ]->setValid(true);

        $io->title(': Doing '.$movie->getMatchName());

        if (!isset($tmdbMovie)){
            //scrap imdb id from imdb url $movie->getImdb
            $imdbId = preg_filter('/^.*\/(tt\d+).*$/','$1',$movie->getImdb());

            //find movie from tmdb based on imdb id
            $tmdbMovieRes = $this->tmdbService->client->getFindApi()->findBy($imdbId,['external_source' => 'imdb_id']);
            if (empty($tmdbMovieRes['movie_results'])){
                $io->error('No TMDB for '.$imdbId);

                $movie->setFetched(false);
                $this->em->flush();
                return;
            }
            $tmdbMovie = $tmdbMovieRes["movie_results"][0];
        }

        //get en/gr version of movie
        /** @var \Tmdb\Model\Movie $modelMovie */
        /** @var \Tmdb\Model\Movie $modelMovieGr */
        $repository = new MovieRepository($this->tmdbService->client);
        try {
            $modelMovie = $repository->load($tmdbMovie['id']);
            $modelMovieGr = $repository->load($tmdbMovie['id'], ['language' => 'el']);
        }catch (\Exception $e){
            $io->error('No TMDB instance for id:'.$tmdbMovie['id']);

            $movie->setFetched(false);
            $this->em->flush();
            return;
        }

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
            if ($this->fast) {
                if ( isset($this->genresLocalArray[ $genre->getId() ]) ) {
                    $movie->addCategory($this->genresLocalArray[ $genre->getId() ]);
                }
            } else {
                $category = $this->em->getRepository(Category::class)->findOneBy(['tmdbId'=>$genre->getId()]);
                if ($category){
                    $movie->addCategory($category);
                }
            }
        }

        //set actors
        foreach($modelMovie->getCredits()->getCast() as $actorModel){

            if ($this->fast) {
                if ( !isset($this->actorsLocalArray[ $actorModel->getId() ]) ) {

                    $actor = new Actor();
                    $this->em->persist($actor);
                    $actor->setName($actorModel->getName());
                    $actor->setTmdbId($actorModel->getId());
                    $actor->setPoster('');
                    if ( !empty($actorModel->getProfilePath()) )
                        $actor->setPoster($actorModel->getProfilePath());
                    $this->actorsLocalArray[ $actorModel->getId() ] = $actor;

                    $io->text('Added new actor ' . $actorModel->getName());
                } else {
                    $io->text('Found actor ' . $actorModel->getName());
                }
            } else {
                $actor = $this->em->getRepository(Actor::class)->findOneBy(['tmdbId'=>$actorModel->getId()]);
                if (!$actor){
                    $actor = new Actor();
                    $this->em->persist($actor);
                    $actor->setName($actorModel->getName());
                    $actor->setTmdbId($actorModel->getId());
                    $actor->setPoster('');
                    if ( !empty($actorModel->getProfilePath()) )
                        $actor->setPoster($actorModel->getProfilePath());

                    $io->text('Added new actor ' . $actorModel->getName());
                }else{
                    $io->text('Found actor ' . $actorModel->getName());
                }
            }

            $movie->addActor($actor);
        }

        //set slug
        $movie->setSlug($this->slugify($movie->getOriginalTitle().'-'.$movie->getYear()));

        $movie->setFetched(true);

        $this->em->flush();

        $perf = $this->scrapper->getPerformance();
        $perf['handle'][] = microtime(true) - $start;
        $this->scrapper->setPerformance($perf);

        $io->success('Added '.$movie->getTitle().'('.$movie->getId().')');
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

    private function determineResults(Movie $movie,array $tmdbMovieRes){

        $amount = count($tmdbMovieRes['results']);

        if ($amount == 0)
            return null;

        foreach ($tmdbMovieRes['results'] as $result){
            //get only year from $result['release_date']
            $year = preg_filter('/^(\d{4}).*$/','$1',$result['release_date']);

            //if not same year continue
            $certainYear = true;
            if ($year != $movie->getYear()) {

                //if year +- 1 is not equal to movie year
                if (
                    $year != $movie->getYear() + 1 &&
                    $year != $movie->getYear() - 1)
                {
                    continue;
                }

                $certainYear = false;
            }

            //slugify titles
            $slugResultTitle = $this->slugify($result['title']);
            $slugOriginalResultTitle = $this->slugify($result['original_title']);
            $slugMovieTile = $this->slugify($movie->getMatchName());

            //if exact match in title
            if ( $slugResultTitle == $slugMovieTile )
                return $result;

            //if exact match in original title
            if ( $slugOriginalResultTitle == $slugMovieTile )
                return $result;

            //up to this point we check only if year is certain
            if (!$certainYear)
                continue;

            //if similar match in title
            similar_text($result['title'],$movie->getMatchName(),$percent);
            if ($percent >= 85 || ($percent > 70 && $amount == 1))
                return $result;

            //leave only digits to title
            $digitsResultTitle = preg_replace('/[^0-9]/', '', $result['title']);
            $digitsMovieTitle = preg_replace('/[^0-9]/', '', $movie->getMatchName());

            //if the digit only title match
            //this takes case of movies like 300, 300: Rise of an Empire
            if ($digitsResultTitle == $digitsMovieTitle)
                return $result;

            var_dump($slugOriginalResultTitle);
            var_dump($slugResultTitle);
            var_dump($slugMovieTile);
            var_dump($percent);
        }

        var_dump($movie->getMatchName());
        var_dump($movie->getYear());
        var_dump($tmdbMovieRes);

        die();
    }
}

