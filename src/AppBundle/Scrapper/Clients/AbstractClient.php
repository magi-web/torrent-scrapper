<?php
/**
 * Created by PhpStorm.
 * User: Pti-Peruv
 * Date: 14/08/2015
 * Time: 21:11
 */

namespace AppBundle\Scrapper\Clients;

use AppBundle\Scrapper\ScrapperInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractClient
 * @package AppBundle\Scrapper\Clients
 */
abstract class AbstractClient implements ScrapperInterface
{
    const MAX_TIMEOUT = 5;
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36';
    const CRT_FILE_PATH = 'E:\Programmes\Cmder\vendor\msysgit\bin\curl-ca-bundle.crt';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var LoggerInterface
     */
    protected $loggerInstance = null;

    protected $rootDir = '';

    /**
     * @var bool
     */
    protected $authenticated = false;

    /**
     * Set Logger instance
     *
     * @param LoggerInterface $loggerService
     */
    public function setLogger(LoggerInterface $loggerService)
    {
        if (empty($loggerService)) {
            echo 'NO LOGGING SERVICE PROVIDED';
            exit;
        }

        $this->loggerInstance = $loggerService;
    }

    /**
     * Set Application Root Directory
     *
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Get the destination directory to save files
     *
     * @return string
     */
    public function getUploadDir()
    {
        $destinationDirectory = $this->rootDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'uploads';
        if (!file_exists($destinationDirectory)) {
            mkdir($destinationDirectory);
        }

        return $destinationDirectory;
    }

    /**
     * Get the links to the remote resources to download
     *
     * @param string $url
     * @return null|array
     */
    public function getLinkForUrl($url)
    {
        if (!$this->authenticated) {
            echo 'User not logged in. Please authenticate first' . "\r\n";

            return null;
        }

        return $this->doGetLink($url);
    }

    /**
     * Download files using the given links
     *
     * @param array $link
     */
    public function downloadFile($link)
    {
        if (!$this->authenticated) {
            echo 'User not logged in. Please authenticate first' . "\r\n";

            return;
        }

        if (!empty($link)) {
            $this->doDownloadFile($link);
        }
    }

    /**
     * Returns certificate file path
     *
     * @return string
     */
    protected function getVerifyPath()
    {
        return self::CRT_FILE_PATH;
    }

    /**
     * Effectively fetch link information related to a url
     *
     * @param $url
     * @return array|null
     */
    protected abstract function doGetLink($url);

    /**
     * Effectively download the remote file
     *
     * @param array $link
     * @return mixed
     */
    protected abstract function doDownloadFile($link);

    /**
     * @param string $rssFeed
     * @return mixed
     */
    public abstract function parseRSS($rssFeed);

    /**
     * Tells if user is authenticated or not
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }
}