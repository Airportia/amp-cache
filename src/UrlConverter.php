<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 23.01.2019
 * Time: 0:42
 */

namespace DigitalBrands\AmpCache;


class UrlConverter
{
    const CONTENT_TYPE_HTML = 'c';
    const CONTENT_TYPE_IMAGE = 'i';
    const CONTENT_TYPE_RESOURCE = 'r';
    private $privateKey;

    public function __construct($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    public function convert($ampUrl, $server, $contentType = self::CONTENT_TYPE_HTML)
    {
        $this->validateUrl($ampUrl);

        $timestamp = time();

        list($scheme, $host, $url) = $this->parseUrl($ampUrl);

        $ampCachePath = "/update-cache/$contentType/" . ($scheme === 'https' ? 's/' : '');
        $ampCachePath .= "{$url}?amp_action=flush&amp_ts={$timestamp}";

        $signature = $this->signPath($ampCachePath);

        return "https://$host.$server$ampCachePath&amp_url_signature={$signature}";
    }

    private function parseUrl($ampUrl)
    {
        $info = parse_url($ampUrl);

        $scheme = $info['scheme'];
        $host = str_replace('.', '-', $info['host']);
        $path = isset($info['path']) ? $info['path'] : '';
        $query = urlencode(isset($info['query']) ? "?{$info['query']}" : '');
        $url = "{$info['host']}{$path}$query";

        return [$scheme, $host, $url];
    }

    private function signPath($path)
    {
        if (!@openssl_sign($path, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            $error = openssl_error_string();
            throw new AmpCacheException("Failed to sign the $path: $error");
        }
        return $this->base64encode($signature);
    }

    private function base64encode($string)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    private function validateUrl($ampUrl)
    {
        $isValid = filter_var($ampUrl, FILTER_VALIDATE_URL, [
            FILTER_FLAG_SCHEME_REQUIRED,
            FILTER_FLAG_HOST_REQUIRED,
        ]);

        if (!$isValid) {
            throw new AmpCacheException('Invalid url. Url must contains scheme and host parts');
        }
    }
}