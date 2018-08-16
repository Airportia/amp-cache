<?php
/**
 * Created by Andrew Ivchenkov <and.ivchenkov@gmail.com>
 * Date: 15.08.18
 */

namespace DigitalBrands\Tests\Amp;

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
        $cache = new \DigitalBrands\Amp\AmpCache(file_get_contents(__DIR__ . '/private-key.pem'));
        $this->expectException(\InvalidArgumentException::class);
        $cache->update('wwww');
    }
}
