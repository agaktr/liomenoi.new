<?php

namespace App\Command;

use App\Entity\Category;
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

class ScrapCategoriesCommand extends Command
{
    protected static $defaultName = 'ScrapCategories';
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

        $io->title('Starting to scrap Categories');

        $categories = $this->scrapper->client->getGenresApi()->getGenres(['language' => 'el-GR']);

        foreach ($categories as $category){
var_dump($category);
            $ourCategory = $this->em->getRepository(Category::class)->findOneBy(['tmdbId' => $category['id']]);

            if (!$ourCategory){
                $ourCategory = new Category();
                $this->em->persist($ourCategory);
                $ourCategory->setTmdbId($category['id']);
            }

            $ourCategory->setName($category['name']);

            $io->title('Doing category '.$category['name']);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}

