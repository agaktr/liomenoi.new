<?php

namespace App\Command;

use App\Entity\Magnet;
use App\Entity\Movie;
use App\Entity\Provider;
use App\Entity\Scrap;
use App\Entity\YifyObject;
use App\Service\ScrapperService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScrapCommand extends Command
{
    protected static $defaultName = 'Scrap';
    protected static $defaultDescription = 'This command scraps objects from YIFY so we can get the torrents.';

    private array $urls = [];
    private array $objectsMap = [];
    private array $objectsLocalArray = [];
    private array $magnetsLocalArray = [];

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

//        ini_set('memory_limit', '2048M');

        $io = new SymfonyStyle($input, $output);

        //Get providers
        $providers = $this->em->getRepository(Provider::class)->findAll();
        foreach ($providers as $provider) {

            $io->title('Provider: ' . $provider->getName());
            $io->title('Starting Movie scrapping');

            //init variables
            $hasMore = true;
            $pagesNo = 5;
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

                    //get local magnets
                    $objectsLocal = $this->em->getRepository(Magnet::class)->findAll();
                    $this->magnetsLocalArray = [];
                    foreach($objectsLocal as $object){
                        $objectKey = $object->getMagnet();
                        $this->magnetsLocalArray[$objectKey] = $object;
                    }
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
                            $this->handleMovie($objectId,$objectData);
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

    private function handleMovie(int $objectId, array $movieData)
    {
        /** @var Movie $movie */
        $objectKey =$movieData[ 'data' ]->getName().'-'.$movieData[ 'data' ]->getYear();
        if(!isset($this->objectsLocalArray[$objectKey])){
            $movie = new Movie();
            $this->em->persist($movie);
            $this->objectsLocalArray[$objectKey] = $movie;
        }else{
            $movie = $this->objectsLocalArray[$objectKey];
        }

        $movie->addScrap($movieData[ 'data' ]);
        $movie->setMatchName($movieData[ 'data' ]->getName());

        $movie->setImdb($movieData[ 'imdb' ]);
        $movie->setSlug($movieData[ 'data' ]->getSlug());
        $movie->setTitle($movieData[ 'data' ]->getName());
        $movie->setYear($movieData[ 'data' ]->getYear());

//        if ( !isset($movieData[ 'magnet' ]) ) {
//            $io->title('magnet__deleting ' . $movie->getId());
//            unset($this->objectsMap[ $movie->getId() ]);
//            unset($this->urls[ $movie->getId() ]);
//            $this->em->remove($movie);
//            $this->em->flush();
//            continue;
//        }

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
    }
}

