<?php
/**
 * Created by PhpStorm.
 * User: ptiperuv
 * Date: 12/06/2016
 * Time: 12:15
 */

namespace AppBundle\Entity;

/**
 * Class Search
 * @package AppBundle\Entity
 */
class Search
{
    const CHANNEL_RSS = 'rss';

    /**
     * @var string
     */
    private $channel;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $quality;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $episode;

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     * @return Search
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return Search
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return Search
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuality()
    {
        return explode("|", $this->quality);
    }

    /**
     * @param string $quality
     * @return Search
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguage()
    {
        return explode('|', $this->language);
    }

    /**
     * @param string $language
     * @return Search
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * @param string $episode
     * @return Search
     */
    public function setEpisode($episode)
    {
        $this->episode = $episode;
        return $this;
    }
}