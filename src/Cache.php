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
use GuzzleHttp\Promise;

class Cache
{
    const DELIMITER = '<::>';

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

    public static function create($privateKey, array $config = [])
    {
        $config = array_merge($config, ['private_key' => $privateKey]);
        return new self(Config::create($config));
    }

    public function update($url, $contentType = UrlConverter::CONTENT_TYPE_HTML)
    {
        $this->updateBatch([$url], $contentType);
    }

    public function updateBatch(array $urls, $contentType = UrlConverter::CONTENT_TYPE_HTML)
    {
        $promises = $this->makePromises($urls, $contentType);

        $results = Promise\settle($promises)->wait();

        $groupedResults = $this->groupResultsByUrl($results);

        foreach ($groupedResults as $url => $result) {
            $failed = [];
            foreach ($result as $server => $serverResult) {
                if ($serverResult['state'] === 'rejected') {
                    /** @var TransferException $e */
                    $e = $serverResult['reason'];
                    $failed[] = "Failed to update $url url on $server: {$e->getMessage()}";
                }
            }

            if ($this->shouldThrowException($failed)) {
                $message = implode(';', $failed);
                throw new ResponseException($message);
            }
        }
    }

    private function shouldThrowException(array $failed)
    {
        if (!$failed) {
            return false;
        }

        if (count($this->getCacheServers()) === 1) {
            return true;
        }

        return $this->config->isExceptionOnGroup() && count($failed) === count($this->getCacheServers());
    }

    private function getCacheServers()
    {
        if (!empty($this->servers)) {
            return $this->servers;
        }

        $this->servers = $this->config->getServers();

        if (empty($this->servers)) {
            try {
                $response = $this->client->get($this->config->getCacheListUrl(),
                    ['timeout' => $this->config->getTimeout()]);
                $servers = \GuzzleHttp\json_decode($response->getBody(), true);
                $this->servers = array_column($servers['caches'], 'updateCacheApiDomainSuffix');
            } catch (\Exception $e) {
                throw new AmpCacheException('Failed to fetch cache servers', 0, $e);
            }
        }

        return $this->servers;
    }

    /**
     * @param $results
     * @return array
     */
    private function groupResultsByUrl($results)
    {
        $groupedResults = [];
        foreach ($results as $index => $result) {
            list($url, $server) = explode(self::DELIMITER, $index);
            $groupedResults[$url][$server] = $result;
        }
        return $groupedResults;
    }

    /**
     * @param array $urls
     * @param $contentType
     * @return array
     */
    private function makePromises(array $urls, $contentType)
    {
        $promises = [];

        foreach ($urls as $url) {
            foreach ($this->getCacheServers() as $server) {
                $index = $url . self::DELIMITER . $server;
                $promises[$index] = $this->client->getAsync($this->urlConverter->convert($url, $server, $contentType),
                    ['timeout' => $this->config->getTimeout()]);
            }
        }
        return $promises;
    }

}