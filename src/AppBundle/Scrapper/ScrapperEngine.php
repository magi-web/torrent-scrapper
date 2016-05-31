<?php

/**
 * Created by PhpStorm.
 * User: Pti-Peruv
 * Date: 14/08/2015
 * Time: 12:07
 */

namespace AppBundle\Scrapper;

use Psr\Log\LoggerInterface;

class ScrapperEngine
{
    /**
     * @var array
     */
    private $supportedClients;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Adds a client to the supported list
     *
     * @param string $serviceTypeName
     * @param string $alias
     */
    public function addClient($serviceTypeName, $alias)
    {
        $this->supportedClients[$alias] = $serviceTypeName;

        $this->logger->debug('Adding new collector client to supported list', ['alias' => $alias]);
    }

    /**
     * Get a client by its alias
     *
     * @param string $alias
     *
     * @return null|ScrapperInterface
     */
    public function getClient($alias)
    {
        $client = null;
        if (!isset($this->supportedClients[$alias])) {
            $this->logger->error('Given alias is not supported by the CollectorEngine', ['alias' => $alias]);
        } else {
            $client = $this->supportedClients[$alias];
        }

        return $client;
    }


    /**
     * Get a client by the torrent url to fetch
     *
     * @param string $url
     *
     * @return null|ScrapperInterface
     */
    public function getClientForUrl($url)
    {
        return $this->getClient('scrapper.client.' . parse_url($url, PHP_URL_HOST));
    }

    /**
     * Get a client by its hostname
     *
     * @param string $host
     * @return ScrapperInterface|null
     */
    public function getClientByHost($host)
    {
        return $this->getClient('scrapper.client.www.' . $host);
    }
}