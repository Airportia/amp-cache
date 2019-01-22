<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 22.01.2019
 * Time: 23:56
 */

namespace DigitalBrands\AmpCache;


class Config
{
    const CACHE_SERVERS_URL = 'https://cdn.ampproject.org/caches.json';

    private $privateKey;
    /**
     * @var array
     */
    private $servers;
    /**
     * @var string
     */
    private $cacheListUrl;

    private $timeout;

    public function __construct($privateKey, $timeout, $cacheListUrl = self::CACHE_SERVERS_URL, array $servers = [])
    {
        $this->privateKey = $privateKey;
        $this->servers = $servers;
        $this->cacheListUrl = $cacheListUrl;
        $this->timeout = $timeout;
    }

    public static function create(array $config)
    {
        $servers = isset($config['servers']) ? $config['servers'] : [];
        $privateKey = isset($config['private_key']) ? $config['private_key'] : '';
        $listUrl = isset($config['cache_list_url']) ? $config['cache_list_url'] : self::CACHE_SERVERS_URL;
        $timeout = isset($config['timeout']) ? $config['timeout'] : 5;

        return new self($privateKey, $timeout, $listUrl, $servers);
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * @return string
     */
    public function getCacheListUrl()
    {
        return $this->cacheListUrl;
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}