<?php
/**
 * Created by Andrew Ivchenkov <and.ivchenkov@gmail.com>
 * Date: 15.08.18
 */

namespace DigitalBrands\Tests\Amp;

use DigitalBrands\Amp\AmpCacheException;
use PHPUnit\Framework\TestCase;

class AmpCacheTest extends TestCase
{
    public function testWrongKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        new \DigitalBrands\Amp\AmpCache('SomeKey');
    }

    public function testWrongUrl()
    {
        $keyFile = __DIR__ . '/private-key.pem';
        if (!is_file($keyFile)) {
            $this->markTestSkipped('The private key file doesn\'t exist');
        }
        $cache = new \DigitalBrands\Amp\AmpCache(file_get_contents($keyFile));
        $this->expectException(\InvalidArgumentException::class);
        $cache->update('wwww');
    }

    public function testBadResponse()
    {
        $keyFile = __DIR__ . '/private-key.pem';
        if (!is_file($keyFile)) {
            $this->markTestSkipped('The private key file doesn\'t exist');
        }
        $cache = new \DigitalBrands\Amp\AmpCache(file_get_contents($keyFile));

        $this->expectException(AmpCacheException::class);
        $this->expectExceptionMessage('Failed to update ');

        $cache->update('http://google.com');
    }
}
