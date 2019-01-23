[![Build Status](https://travis-ci.org/DigitalBrands/amp-cache.svg?branch=master)](https://travis-ci.org/DigitalBrands/amp-cache)
# Amp Update Cache library

Sometimes you need to tell Google to update cache of your AMP page.
Read more: https://developers.google.com/amp/cache/update-cache

This library is an easy tool to do it. All you need is Private Api Key and url you'd like to update.

## Usage

```php
<?php
$ampCache = \DigitalBrands\AmpCache\Cache::create('<YourPrivateApiKey>');

$ampCache->update('<YourAMPUrl>');
//or
$ampCache->updateBatch(['<YourAMPUrl1>', <YourAMPUrl2>])
```

### Configuration

```php

$config=[
    'cache_list_url' => 'https://cdn.ampproject.org/caches.json', //A url servers list will be downloaded from. Default https://cdn.ampproject.org/caches.json
    'servers' => ['cdn.ampproject.org'], //an array of cache servers. Then client will not download them from cache list url
    'timeout' => 5, //A timeout for updating single url on particular cache server. Default 5.
    'exception_on_group' => false, //If true then exception will be thrown only when all cache servers return bad response (not 200 code). If there is only one server in cache, then this option will be ignored
];

$ampCache = \DigitalBrands\AmpCache\Cache::create('<YourPrivateApiKey>', $config);

```

