<?php
/**
 * Created by PhpStorm.
 * User: Pti-Peruv
 * Date: 25/08/2015
 * Time: 23:19
 */

namespace AppBundle\Scrapper\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

/**
 * Class T411Client
 * @package AppBundle\Scrapper\Clients
 */
class T411Client extends AbstractClient
{
    const BASE_URI = 'http://www.t411.ch';

    /**
     * @inheritdoc
     */
    protected function doGetLink($url)
    {
        $result = null;
        $response = $this->httpClient->get(
            $url
        );

        $pageContent = $response->getBody()->getContents();
        if (preg_match('/\/torrents\/download\/\?id=(\d*)">(\S*).torrent</i', $pageContent, $links)) {
            $result = ["http://www.t411.ch/torrents/download/?id=" . $links[1], $links[2]];
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function doDownloadFile($link)
    {
        $response = $this->httpClient->get($link[0], ['save_to' => $this->getUploadDir() . DIRECTORY_SEPARATOR . $link[1] . ".torrent"]);
        if ($response->getStatusCode() != 200) {
            $this->loggerInstance->error('Error while accessing ' . $link[0]);
        }
    }

    /**
     * @inheritdoc
     */
    public function authenticate($login, $password)
    {
        $this->httpClient = new Client([
            'base_uri'        => self::BASE_URI,
            'verify'          => $this->getVerifyPath(),
            'cookies'         => true,
            'headers'         => [
                'User-Agent' => self::USER_AGENT,
            ],
            'timeout'         => self::MAX_TIMEOUT, // Response timeout
            'connect_timeout' => self::MAX_TIMEOUT, // Connection timeout
        ]);

        try {
            $response = $this->httpClient->post(
                '/users/login',
                [
                    'form_params' => [
                        'login' => $login,
                        'password' => $password
                    ],
                    'headers' => [
                        'Accept' => '/*',
                        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept-Language' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4,da;q=0.2'
                    ]
                ]
            );

            $pageContent = $response->getBody()->getContents();
            if (($this->authenticated = (strpos($pageContent, '<a href="/users/profile/">Mon Compte</a>') !== false))) {
                $this->loggerInstance->debug('User logged in');
            } else {

                $this->loggerInstance->error('Error during authentication. Please check your credentials');
                $this->httpClient = null;
            }
        } catch (ConnectException $e) {
            $this->loggerInstance->error(
                'Connection timed out while trying to login.',
                array('timeout' => AbstractClient::MAX_TIMEOUT, 'base_uri' => self::BASE_URI)
            );

            $this->httpClient = null;
        } catch (ClientException $e) {
            $this->loggerInstance->debug($e->getResponse()->getBody()->getContents());
            $this->loggerInstance->error($e->getMessage(), $e->getRequest()->getHeaders());

            $this->httpClient = null;
        }

        return $this->httpClient;
    }

    /**
     * @inheritdoc
     */
    function logout ()
    {
        $response= $this->httpClient->get('/users/logout/');
        $pageContent = $response->getBody()->getContents();
        $this->authenticated = (strpos($pageContent, "<a href=\"/users/profile/\">Mon Compte</a>") === false);
    }

    /**
     * @param string $rssFeed
     * @return mixed
     */
    public function parseRSS($rssFeed)
    {
        //$response = $this->httpClient->get($rssFeed);

        //$pageContent = $response->getBody()->getContents();
        $rssFeedElement = simplexml_load_file($rssFeed);
        $this->loggerInstance->debug(print_r($rssFeedElement, true));
    }
}