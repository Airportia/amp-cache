<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 23.01.2019
 * Time: 0:38
 */

namespace DigitalBrands\AmpCache\Test;

use DigitalBrands\AmpCache\AmpCacheException;
use DigitalBrands\AmpCache\UrlConverter;
use PHPUnit\Framework\TestCase;

class UrlConverterTest extends TestCase
{
    public function test()
    {
        $inputUrl = 'https://ampbyexample.com/components/amp-img';
        $assertUrl = '#^https://ampbyexample-com\.cdn\.ampproject\.org/update-cache/c/s/ampbyexample\.com/components/amp-img\?amp_action=flush&amp_ts=\d+&amp_url_signature=.+$#';
        $server = 'cdn.ampproject.org';

        $converter = $this->createConverter();


        $this->assertRegExp($assertUrl, $converter->convert($inputUrl, $server));
    }

    public function testInvalidUrl()
    {
        $converter = $this->createConverter();

        $this->expectException(AmpCacheException::class);
        $this->expectExceptionMessage('Invalid url. Url must contains scheme and host parts');

        $converter->convert('asd', 'cdn.ampproject.org');
    }

    private function createConverter()
    {
        return new UrlConverter(file_get_contents(__DIR__ . '/_support/key.pem'));
    }
}