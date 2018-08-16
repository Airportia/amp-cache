<?php
/**
 * Created by Andrew Ivchenkov <and.ivchenkov@gmail.com>
 * Date: 15.08.18
 */

namespace DigitalBrands\Tests\Amp;

use DigitalBrands\Amp\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testGetCacheServers()
    {
        $connection = new Connection();
        $servers = $connection->getCacheServers();

        $this->assertNotEmpty($servers);

        foreach ($servers as $server) {
            $this->assertTrue(isset($server['id']));
            $this->assertTrue(isset($server['name']));
            $this->assertTrue(isset($server['docs']));
            $this->assertTrue(isset($server['updateCacheApiDomainSuffix']));
        }
    }

    public function testSend()
    {
        $connection = $this->createClient([new Response(200, [], 'NotOk')]);

        $this->assertEquals('NotOk', $connection->send('aaa'));
    }

    /**
     * @param array $responses
     * @return Connection
     */
    private function createClient(array $responses)
    {
        $mock = new MockHandler($responses);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new Connection($client);
    }
}
