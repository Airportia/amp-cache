<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 22.01.2019
 * Time: 23:47
 */

namespace DigitalBrands\AmpCache\Tests;


use DigitalBrands\AmpCache\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    public function testKey()
    {
        $key = '123';
        $config = Config::create([
            'private_key' => $key
        ]);

        $this->assertEquals('123', $config->getPrivateKey());
    }

    public function testServers()
    {
        $servers = ['123', '12345'];
        $config = Config::create([
            'servers' => $servers
        ]);

        $this->assertEquals($servers, $config->getServers());
    }

    public function testCacheListUrl()
    {
        $config = Config::create([
            'cache_list_url' => 'aaaa'
        ]);

        $this->assertEquals('aaaa', $config->getCacheListUrl());
    }

    public function testTimeout()
    {
        $config = Config::create(['timeout' => 10]);

        $this->assertEquals(10, $config->getTimeout());
    }

}