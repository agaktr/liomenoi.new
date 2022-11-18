<?php

namespace App\Command;

use App\Entity\Magnet;
use App\Entity\Movie;
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

        $currentPage = 1;

        while($currentPage < 99999999){

            $io->title('Doing page '.$currentPage);

            ++$currentPage;


            $objects = $this->em->getRepository(YifyObject::class)->findBy(['fetched' => false],[ 'id' => 'ASC'], 25,0);

            $objectsMap = [];
            foreach ($objects as $object) {
                $objectsMap[$object->getId()] = $object;
                $this->urls[$object->getId()] = 'https://yts.do'.$object->getSlug();
            }

            $this->scrapper->setUrls($this->urls);

            try {
                $this->scrapper->initObjects();
            } catch (\Exception $e) {
                $io->title('retry '.$currentPage);
                continue;
            }


            $results = $this->scrapper->getScrappedContent();

            foreach ($results as $id => $content) {

                $results[$id]['data'] = $objectsMap[$id];

            }

            $added = $updated = 0;
            $addedMagnet = $updatedMagnet = 0;

            foreach ($results as $objectId => $movieData) {

                /** @var Movie $movie */
                $movie = $this->em->getRepository(Movie::class)->findOneBy(['slug' => $movieData['data']->getSlug()]);

                if (!$movie) {
                    ++$added;
                    $movie = new Movie();
                    $this->em->persist($movie);
                }else{
                    ++$updated;
                }

                $movie->setImdb($movieData['imdb']);
                $movie->setSlug($movieData['data']->getSlug());
                $movie->setTitle($movieData['data']->getTitle());
                $movie->setYear($movieData['data']->getYear());

                foreach ($movieData['magnet'] as $magnetLink) {

                    /** @var Magnet $magnet */
                    $magnet = $this->em->getRepository(Magnet::class)->findOneBy(['magnet' => $magnetLink['magnet']]);

                    if (!$magnet) {
                        ++$addedMagnet;
                        $magnet = new Magnet();
                        $this->em->persist($magnet);
                    }else{
                        ++$updatedMagnet;
                    }

                    $magnet->setType($magnetLink['type']);
                    $magnet->setQuality($magnetLink['quality']);
                    $magnet->setSize($magnetLink['size']);
                    $magnet->setMagnet($magnetLink['magnet']);
                    $magnet->setMovie($movie);
                }

                $objectsMap[$objectId]->setFetched(true);
            }

            $this->em->flush();

            $content = sprintf('ScrapYIFY: %s objects added. %s objects updated. %s magnets added. %s magnets updated. DONE Time: %s', $added,$updated,$addedMagnet,$updatedMagnet,json_encode($this->scrapper->getPerformance()));

            $io->success($content);

            unset($this->urls);

        }

        return Command::SUCCESS;
    }
}

