<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 22.01.2019
 * Time: 23:55
 */

namespace DigitalBrands\AmpCache;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;

class Cache
{
    const CACHE_SERVERS_URL = 'https://cdn.ampproject.org/caches.json';

    /** @var Config */
    private $config;

    /** @var ClientInterface */
    private $client;

    /** @var UrlConverter */
    private $urlConverter;

    private $servers;

    public function __construct(Config $config, ClientInterface $client = null)
    {
        $this->config = $config;
        $this->client = $client === null ? new Client() : $client;
        $this->urlConverter = new UrlConverter($config->getPrivateKey());
    }

    public static function create($privateKey)
    {
        $config = Config::create(['private_key' => $privateKey]);
        return new self($config);
    }

    public function update($url, $contentType = UrlConverter::CONTENT_TYPE_HTML)
    {
        $failed = [];
        foreach ($this->getCacheServers() as $server) {
            try {
                $this->client->get($this->urlConverter->convert($url, $server, $contentType),
                    ['timeout' => $this->config->getTimeout()]);
            } catch (TransferException $e) {
                $failed[] = "Failed to update $url url on $server: {$e->getMessage()}";
            }
        }

        if ($failed) {
            $message = implode(';', $failed);
            throw new ResponseException($message);
        }
    }

    private function getCacheServers()
    {
        if (!empty($this->servers)) {
            return $this->servers;
        }

        $this->servers = $this->config->getServers();

        if (empty($this->servers)) {
            try {
                $response = $this->client->get(self::CACHE_SERVERS_URL, ['timeout' => $this->config->getTimeout()]);
                $servers = \GuzzleHttp\json_decode($response->getBody(), true);
                $this->servers = array_column($servers['caches'], 'updateCacheApiDomainSuffix');
            } catch (\Exception $e) {
                throw new AmpCacheException('Failed to fetch cache servers', 0, $e);
            }
        }

        return $this->servers;
    }

}