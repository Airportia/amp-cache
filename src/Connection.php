<?php
/**
 * Created by Andrew Ivchenkov <and.ivchenkov@gmail.com>
 * Date: 15.08.18
 */

namespace DigitalBrands\Amp;

use GuzzleHttp\Client;

class Connection
{
    const CACHE_SERVERS_URL = 'https://cdn.ampproject.org/caches.json';

    private $client;

    /**
     * Connection constructor.
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client === null ? new Client() : $client;
    }

    public function getCacheServers()
    {
        try {
            $response = $this->client->get(self::CACHE_SERVERS_URL);
            $json = \GuzzleHttp\json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->throwException('Failed to fetch cache servers', $e);
        }
        return $json['caches'];
    }

    public function send($url)
    {
        try {
            $response = $this->client->get($url);
        } catch (\Exception $e) {
            $this->throwException('Failed to send update command', $e);
        }
        if ((string)$response->getBody() !== 'OK') {
            $this->throwException('Cache update failed. Bad server response: ' . $response->getBody());
        }
    }

    private function throwException($message, \Throwable $prev = null)
    {
        throw new ConnectionException($message, 0, $prev);
    }
}
