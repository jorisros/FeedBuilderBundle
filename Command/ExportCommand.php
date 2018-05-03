<?php

namespace FeedBuilderBundle\Command;

use FeedBuilderBundle\Service\FeedBuilderService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
        $feedName = $input->getArgument('feed_id');
        $profile = FeedBuilderService::getConfigOfProfile($feedName);

        if(empty($profile)){
            $config = FeedBuilderService::getConfig();
            $availableFeeds = $config->get('feeds');
            $availableFeedTitles = [];
            foreach ($availableFeeds as $availableFeed){
                $availableFeedTitles[] = $availableFeed->get('title');
            }

            $helper = $this->getHelper('question');
            $question = new Question('Please enter the name of the feed. Available options: '.implode(', ', $availableFeedTitles)."\n", 'products');
            $feedName = $helper->ask($input, $output, $question);

            if(!in_array($feedName, $availableFeedTitles)){
                throw new \InvalidArgumentException('Invalid feed');
            }

            $profile = FeedBuilderService::getConfigOfProfile($feedName);
        }

        $feedbuilder = new FeedBuilderService($this->getContainer()->get('event_dispatcher'));
        $feedbuilder->run($profile);
        $output->writeln("$feedName successfully exported");
    }

}
