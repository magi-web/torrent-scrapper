<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Transmission\Transmission;

class TransmissionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('torrent:add-to-seedbox')
            ->setDescription('')
            ->addArgument(
                'torrent_file',
                InputArgument::REQUIRED,
                'The torrent url'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Transmission $transmission */
        $transmission = $this->getContainer()->get('transmission');
        $torrentToAdd = $this->getContainer()->get('kernel')->getRootDir() . '/../web/uploads/' . $input->getArgument('torrent_file');

        if (($content = file_get_contents($torrentToAdd)) !== false) {
            $metaInfo = base64_encode($content);
            $transmission->add($metaInfo, true, $this->getContainer()->getParameter('transmission_home_directory') . 'series');
        }
    }
}
