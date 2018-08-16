[![Build Status](https://travis-ci.org/DigitalBrands/amp-cache.svg?branch=master)](https://travis-ci.org/DigitalBrands/amp-cache)
## Amp Update Cache library

Sometimes you need to tell Google to update cache of your AMP page.
Read more: https://developers.google.com/amp/cache/update-cache

This library is an easy tool to do it. All you need is Private Api Key and url you'd like to update.

### Usage

```php
<?php
$ampCache = new \DigitalBrands\Amp\AmpCache('<YourPrivateApiKey>');
$ampCache->update('<YourAMPUrl>');
```

