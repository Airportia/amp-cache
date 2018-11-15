<?php
/**
 * Created by Andrew Ivchenkov <and.ivchenkov@gmail.com>
 * Date: 15.08.18
 */

namespace DigitalBrands\Amp;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AmpCache
{
    const CONTENT_TYPE_HTML = 'c';
    const CONTENT_TYPE_IMAGE = 'i';
    const CONTENT_TYPE_RESOURCE = 'r';

    private $privateKey;
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $cacheServers;
    private $connection;

    /**
     * Cleaner constructor.
     * @param                      $privateKey
     * @param LoggerInterface|null $logger
     */
    public function __construct($privateKey, LoggerInterface $logger = null)
    {
        if (!$this->privateKey = openssl_pkey_get_private($privateKey)) {
            $message = 'Invalid private key';
            $this->log($message, LogLevel::ERROR);
            throw new \InvalidArgumentException($message);
        }
        $this->connection = new Connection();
        $this->logger = $logger;
    }

    public function update($ampUrl, $contentType = self::CONTENT_TYPE_HTML)
    {
        $this->validateUrl($ampUrl);
        $this->log("Updating AMP cache for $ampUrl\n");
        $urls = $this->getCacheUrls($ampUrl, $contentType);

        foreach ($urls as $url) {
            $response = $this->connection->send($url);
            if ($response !== 'OK' || $response !== 'Not Found') {
                $message = "Failed to update $ampUrl cache: $response";
                $this->log($message, LogLevel::ERROR);
                throw new AmpCacheException($message);
            }
        }
    }

    private function getCacheUrls($ampUrl, $contentType)
    {
        $result = [];
        $timestamp = time();

        list($scheme, $host, $url) = $this->parseUrl($ampUrl);

        $ampCachePath = "/update-cache/$contentType/" . ($scheme === 'https' ? 's/' : '');
        $ampCachePath .= "{$url}?amp_action=flush&amp_ts={$timestamp}";

        $this->log("Amp cache path: $ampCachePath");

        $signature = $this->signPath($ampCachePath);

        foreach ($this->getCacheServers() as $server) {
            $ampCacheBase = "https://$host.{$server['updateCacheApiDomainSuffix']}";
            $cacheUrl = "{$ampCacheBase}{$ampCachePath}&amp_url_signature={$signature}";
            $this->log("Cache url: $cacheUrl");
            $result[] = $cacheUrl;
        }

        return $result;
    }

    private function signPath($path)
    {
        if (!@openssl_sign($path, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            throw new AmpCacheException("Failed to sign the $path");
        }
        return $this->base64encode($signature);
    }

    private function base64encode($string)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    private function getCacheServers()
    {
        if ($this->cacheServers === null) {
            $this->log('Getting cache servers');
            $this->cacheServers = $this->connection->getCacheServers();
        }

        return $this->cacheServers;
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

    private function log($string, $level = LogLevel::DEBUG)
    {
        if ($this->logger === null) {
            return;
        }

        $this->logger->log($level, $string);
    }

    private function validateUrl($ampUrl)
    {
        $isValid = filter_var($ampUrl, FILTER_VALIDATE_URL, [
            FILTER_FLAG_SCHEME_REQUIRED,
            FILTER_FLAG_HOST_REQUIRED,
        ]);

        if (!$isValid) {
            $message = 'Invalid url. Url must contains scheme and host parts';
            $this->log($message, LogLevel::ERROR);
            throw new \InvalidArgumentException($message);
        }
    }
}
