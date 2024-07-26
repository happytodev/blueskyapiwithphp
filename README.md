# A PHP Package to interact with Bluesky API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/happytodev/blueskyapiwithphp.svg?style=flat-square)](https://packagist.org/packages/happytodev/blueskyapiwithphp)
[![Tests](https://img.shields.io/github/actions/workflow/status/happytodev/blueskyapiwithphp/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/happytodev/blueskyapiwithphp/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/happytodev/blueskyapiwithphp.svg?style=flat-square)](https://packagist.org/packages/happytodev/blueskyapiwithphp)

A package to interact with Bluesky API with PHP.


## Support me

I invest a lot of time to create many things for the community. You can [sponsor me](https://github.com/sponsors/happytodev) if you want.


## Installation

You can install the package via composer:

```bash
composer require happytodev/blueskyapiwithphp
```

## Usage

A little example with Laravel but you can use in plain PHP or with your favorite framework too. It's up to you.

```php
<?php

namespace App\Http\Controllers;

use Happytodev\Blueskyapiwithphp\Blueskyapiwithphp;

class BlueskyController extends Controller
{
    protected $blueskyApi;

    public function __construct()
    {
        $this->blueskyApi = new Blueskyapiwithphp(config('services.bluesky.api_key'));
    }

    public function showLikes($handle, $postId)
    {
        $likes = $this->blueskyApi->getPostLikes($handle, $postId);

        return view('likes', compact('likes'));
    }

    public function showPostLikesNumber($handle, $postId)
    {
        $likesCount = $this->blueskyApi->getPostLikesCount($handle, $postId);
        $repostsCount = $this->blueskyApi->getPostRepostsCount($handle, $postId);
        $repliesCount = $this->blueskyApi->getPostRepliesCount($handle, $postId);

        dd($likesCount, $repostsCount, $repliesCount);
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Frédéric Blanc](https://github.com/happytodev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
