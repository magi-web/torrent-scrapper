<?php

namespace AppBundle\Command;

use AppBundle\Entity\Search;
use AppBundle\Scrapper\ScrapperEngine;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Transmission\Transmission;

class SearchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('torrent:search')
            ->setDescription('Performs a search for torrents among the available scrappers')
            ->addArgument('query',
                InputArgument::REQUIRED,
                'The search term')
            ->addOption('client', null,
                InputOption::VALUE_OPTIONAL,
                'Use a specific scrapper')
            ->addOption('channel', null,
                InputOption::VALUE_OPTIONAL,
                'Use rss stream',
                Search::CHANNEL_RSS)
            ->addOption('category', null,
                InputOption::VALUE_OPTIONAL,
                'Specify a category')
            ->addOption('episode', null,
                InputOption::VALUE_OPTIONAL,
                'Specify an episode (ex : s06e08)')
            ->addOption('language', null,
                InputOption::VALUE_OPTIONAL,
                'Specify a language (truefrench, vostfr)')
            ->addOption('quality', null,
                InputOption::VALUE_OPTIONAL,
                'Specify a video quality (720p, 1080i, 1080p)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $search = new Search();
        $search->setQuery($input->getArgument('query'))
            ->setChannel($input->getOption('channel'))
            ->setCategory($input->getOption('category'))
            ->setEpisode($input->getOption('episode'))
            ->setLanguage($input->getOption('language'))
            ->setQuality($input->getOption('quality'));

        var_dump($search);

        /** @var ScrapperEngine $scrapperEngine */
        $scrapperEngine = $this->getContainer()->get('app.scrapper.engine');
        $client = $scrapperEngine->getClientByHost($input->getOption('client'));

        $torrents = $client->search($search);

        /** @var Transmission $transmission */
        $transmission = $this->getContainer()->get('transmission');

        foreach ($torrents as $torrent) {
            $torrentLink = $client->getLinkForUrl($torrent);
            $torrentToAdd = $client->getUploadDir() . DIRECTORY_SEPARATOR . $torrentLink[1] . '.torrent';
            if (!file_exists($torrentToAdd)) {
                $client->downloadFile($torrentLink);

                if (($content = file_get_contents($torrentToAdd)) !== false) {
                    $metaInfo = base64_encode($content);
                    $transmission->add($metaInfo, true, $this->getContainer()->getParameter('transmission_home_directory') . 'series');
                }
            }
        }
    }
}
