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

    public function addClient($serviceTypeName, $alias)
    {
        $this->supportedClients[$alias] = $serviceTypeName;

        $this->logger->debug('Adding new collector client to supported list', ['alias' => $alias]);
    }

    /**
     * @param $alias
     *
     * @return null|\AppBundle\Scrapper\ScrapperInterface
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
     * @param $url
     *
     * @return null|\AppBundle\Scrapper\ScrapperInterface
     */
    public function getClientForUrl($url)
    {
        return $this->getClient('scrapper.client.' . parse_url($url, PHP_URL_HOST));
    }
}