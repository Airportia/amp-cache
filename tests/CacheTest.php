<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 23.01.2019
 * Time: 0:31
 */

namespace DigitalBrands\AmpCache\Test;


use DigitalBrands\AmpCache\Cache;
use DigitalBrands\AmpCache\Config;
use DigitalBrands\AmpCache\ResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    public function testRemoteFileList()
    {
        $historyContainer = [];
        $client = $this->createGuzzle($historyContainer, [
            new Response(200, [], file_get_contents(__DIR__ . '/_support/cacheList.json')),
            new Response(200),
            new Response(200),
            new Response(200),
        ]);

        $cache = new Cache($this->createConfig(), $client);
        $cache->update('https://example.com');

        /** @var \GuzzleHttp\Psr7\Request $request1 */
        $request1 = $historyContainer[1]['request'];
        $this->assertEquals('GET', $request1->getMethod());
        $this->assertEquals('example-com.cdn.ampproject.org', $request1->getUri()->getHost());

        /** @var \GuzzleHttp\Psr7\Request $request2 */
        $request2 = $historyContainer[2]['request'];
        $this->assertEquals('GET', $request2->getMethod());
        $this->assertEquals('example-com.amp.cloudflare.com', $request2->getUri()->getHost());

        /** @var \GuzzleHttp\Psr7\Request $request3 */
        $request3 = $historyContainer[3]['request'];
        $this->assertEquals('GET', $request3->getMethod());
        $this->assertEquals('example-com.bing-amp.com', $request3->getUri()->getHost());
    }

    public function testLocalFileList()
    {
        $historyContainer = [];
        $client = $this->createGuzzle($historyContainer, [
            new Response(200),
        ]);

        $cache = new Cache($this->createConfig([
            'my.amp.com'
        ]), $client);
        $cache->update('https://example.com');

        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $historyContainer[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('example-com.my.amp.com', $request->getUri()->getHost());
    }

    public function testBadResponse()
    {
        $historyContainer = [];
        $client = $this->createGuzzle($historyContainer, [
            new Response(400),
        ]);

        $cache = new Cache($this->createConfig(['my.amp.com']), $client);

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Failed to update https://example.com url');

        $cache->update('https://example.com');
    }

    private function createConfig($servers = [])
    {
        $config = Config::create([
            'private_key' => file_get_contents(__DIR__ . '/_support/key.pem'),
            'servers' => $servers
        ]);

        /** @var Config $config */
        return $config;
    }

    private function createGuzzle(&$historyContainer, array $responses = null)
    {

        $history = Middleware::history($historyContainer);
        $mock = new MockHandler($responses);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        return new Client(['handler' => $stack]);
    }

}