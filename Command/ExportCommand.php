<?php

namespace FeedBuilderBundle\Command;

use FeedBuilderBundle\Service\FeedBuilderService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('export:run')
            ->setDescription('Run the feedbuilder by ID to execute the export')
            ->addArgument('feed_id', InputArgument::OPTIONAL, 'Give the feed id to run export')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('feed_id');

        //@TODO Fix the name of the profile to make it dynamic from cli
        $profile = FeedBuilderService::getConfigOfProfile(1);

        $feedbuilder = new FeedBuilderService($this->getContainer()->get('event_dispatcher'));
        $feedbuilder->run($profile);
        $output->writeln('Command result.');
    }

}
