<?php
/**
 * Created by PhpStorm.
 * User: Pti-Peruv
 * Date: 25/08/2015
 * Time: 23:19
 */

namespace AppBundle\Scrapper\Clients;

use AppBundle\Entity\Search;
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
     * @param string $category
     *
     * @return int
     */
    private function getSubCatForCategory($category)
    {
        $subcat = 0;
        $mapping = ['films/video' => 410, 'series' => 433];
        if (key_exists($category, $mapping)) {
            $subcat = $mapping[$category];
        }

        return $subcat;
    }

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
    public function authenticate($login = null, $password = null)
    {
        if (is_null($login)) {
            $login = $this->username;
        }
        if (is_null($password)) {
            $password = $this->password;
        }

        $this->httpClient = new Client([
            'base_uri' => self::BASE_URI,
            'verify' => $this->getVerifyPath(),
            'cookies' => true,
            'headers' => [
                'User-Agent' => self::USER_AGENT,
            ],
            'timeout' => self::MAX_TIMEOUT, // Response timeout
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
    function logout()
    {
        $response = $this->httpClient->get('/users/logout/');
        $pageContent = $response->getBody()->getContents();
        $this->authenticated = (strpos($pageContent, "<a href=\"/users/profile/\">Mon Compte</a>") === false);
    }

    /**
     * @param string $rssFeed
     * @return mixed
     */
    public function parseRSS($rssFeed)
    {
        $rssFeedElement = simplexml_load_file($rssFeed);
        $items = $rssFeedElement->xpath('/rss/channel/item/enclosure');
        $this->loggerInstance->debug(print_r($items, true));

        $links = [];
        foreach ($items as $item) {
            $links[] = (string)$item->attributes()->url;
        }

        return $links;
    }

    /**
     * Performs a search
     *
     * @param Search $search
     * @return array
     */
    public function search(Search $search)
    {
        $result = [];
        if ($search->getChannel() === Search::CHANNEL_RSS) {
            $rssUrl = self::BASE_URI . '/rss/';
            if (!empty($search->getCategory()) && ($subcat = $this->getSubCatForCategory($search->getCategory())) !== 0) {
                $rssUrl .= '?cat=' . $subcat;
            }

            $rssLinks = $this->parseRSS($rssUrl);
            $bestVote = 0;
            $bestLink = '';
            foreach ($rssLinks as $link) {
                $vote = 0;
                if (stripos($link, $search->getQuery()) !== FALSE) {
                    $vote++;

                    if (!empty($search->getEpisode()) && stripos($link, $search->getEpisode())) {
                        $vote++;
                    }
                    if (!empty($search->getLanguage())) {
                        $vote += $this->getVote($link, $search->getLanguage());
                    }
                    if (!empty($search->getQuality())) {
                        $vote += $this->getVote($link, $search->getQuality());
                    }

                    if ($vote > $bestVote) {
                        $bestVote = $vote;
                        $bestLink = $link;
                    }
                }
            }

            if (!empty($bestLink)) {
                $result [] = $bestLink;
            }
        }
        return $result;
    }

    /**
     * @param string $link
     * @param array $criterias
     *
     * @return int
     */
    private function getVote($link, $criterias)
    {
        $vote = 0;

        if (is_array($criterias)) {
            for ($i = 0, $len = count($criterias); $i < $len; $i++) {
                $criteria = $criterias[$i];
                if (stripos($link, $criteria) !== FALSE) {
                    $vote = $i;
                }
            }
        }

        return $vote;
    }
}