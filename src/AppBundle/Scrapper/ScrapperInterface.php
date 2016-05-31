<?php
/**
 * Created by PhpStorm.
 * User: Pti-Peruv
 * Date: 14/08/2015
 * Time: 12:10
 */

namespace AppBundle\Scrapper;


use AppBundle\Entity\Search;

interface ScrapperInterface
{
    /**
     * Authenticate the user to the platform
     *
     * @param string $login
     * @param string $password
     *
     * @return null|\GuzzleHttp\Client
     */
    public function authenticate($login = null, $password = null);

    /**
     * Logout the user from the platform
     */
    public function logout();

    /**
     * Return client authentication status
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Get the links to the remote resources to download
     *
     * @param string $url
     * @return array
     */
    public function getLinkForUrl($url);

    /**
     * Download files using the given links
     *
     * @param string array
     */
    public function downloadFile($link);

    /**
     * Parse an rss feed
     *
     * @param $rssFeed
     * @return mixed
     */
    public function parseRSS($rssFeed);

    /**
     * Performs a search
     *
     * @param Search $search
     * @return array
     */
    public function search(Search $search);

    /**
     * Get the destination directory to save files
     *
     * @return string
     */
    public function getUploadDir();
}