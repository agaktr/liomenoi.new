<?php

namespace App\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScrapYIFYCommand extends Command
{
    protected static $defaultName = 'ScrapYIFY';
    protected static $defaultDescription = 'This command scraps objects from YIFY so we can get the torrents.';

    private EntityManagerInterface $em;


    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->em = $entityManager;

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

        $content = sprintf('ScrapYIFY: %s objects added. DONE Time: %s', count([]),date('Y-m-d H:i:s',time()));

        $io->success($content);

        return Command::SUCCESS;
    }
}

