<?php
/**
 * Created by PhpStorm.
 * User: ptiperuv
 * Date: 31/05/2016
 * Time: 23:12
 */

namespace AppBundle\Command;

use AppBundle\Scrapper\ScrapperEngine;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TorrentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('torrent:fetch')
            ->setDescription('Fetches a torrent file')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'The torrent url'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        if ($url) {
            /** @var ScrapperEngine $scrapperEngine */
            $scrapperEngine = $this->getContainer()->get('app.scrapper.engine');

            $client = $scrapperEngine->getClientForUrl($url);

            $client->parseRSS('http://www.t411.ch/rss/?cat=433');
            /*
            $t411_auth = $this->getContainer()->getParameter('t411');
            $client->authenticate($t411_auth['login'], $t411_auth['password']);
            $output->writeln("user is logged in : " . json_encode($client->isAuthenticated()));

            $torrentLink = $client->getLinkForUrl($url);
            $output->writeln("torrent : " . $torrentLink[0]);

            $client->downloadFile($torrentLink);

            $client->logout();
            $output->writeln("user is logged out : " . json_encode($client->isAuthenticated()));
            */
        }
    }
}