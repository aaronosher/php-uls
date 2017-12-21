# VATUSA ULS Handling for PHP >=7.1.0

[![PHP from Packagist](https://img.shields.io/packagist/php-v/vatusa/laravel-uls.svg?style=flat-square)]()
[![Latest Stable Version](https://poser.pugx.org/vatusa/laravel-uls/v/stable?format=flat-square)](https://packagist.org/packages/vatusa/laravel-uls)
[![Total Downloads](https://poser.pugx.org/vatusa/laravel-uls/downloads?format=flat-square)](https://packagist.org/packages/vatusa/laravel-uls)
[![GitHub license](https://img.shields.io/github/license/vatusa/laravel-uls.svg?style=flat-square)](https://github.com/VATUSA/laravel-uls/blob/master/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/vatusa/laravel-uls.svg?style=flat-square)](https://github.com/vatusa/laravel-uls/issues)

## About

Provides a wrapper around the web-token libraries for use with VATUSA's Unified Login Scheme.

## Installation

1. Require the `vatusa/php-uls` package in your `composer.json` and update your dependencies:
    ```sh
    $ composer require vatusa/laravel-uls
    ```
2. Generate the configuration file
    

## Configuration

For php-uls to work you need to configure your jwk, uls version, and facility id. These are set through an options array in the constructor:
```php
$options = [
    "version" => 2,
    "jwk" => $jwk,
    "facility" => "ZZZ"
];
$uls = new \Vatusa\Uls\Uls($options);
```

## Usage

Using laravel-uls is fairly easy.

1. Get your JSON Web Key from your facility's Technical Configuration page.  https://www.vatusa.net/mgt/facility (**NOTE**: You must hold a ATM, DATM or WM role for that facility to generate/see the generated JSON Web Key)
2. Store the JWK, unedited, in the config above (or, *recommended* quoted with single quotes in the .env file as ULS_JWK='... JWK from VATUSA...')
3. To generate the redirect url, use:
    ```php
    $uls->redirectUrl()
    ```
    To handle the developmental returns, specify a boolean argument of true
    ```php
    $uls->redirectUrl(true)
    ```
4. To verify a token, assume $token is the full token received from VATUSA's ULS endpoint
    ```php
    $uls = new Uls($options);
    if ($uls->verifyToken($token)) {
       // Token was true
    }
    ```
    
    The laravel-uls library conducts header verifications to ensure that the accepted algorithms
    are received.  Additionally, it conducts the following claims checks, including:
    * Ensures the audience is you (IE, the token isn't meant for another facility)
    * The token is not expired
    * The Issued at time is logical (ie, not in the future)
    
    Because of this, a number of exceptions may be thrown:
    * InvalidArgumentException
    * Jose\Component\Checker\InvalidClaimException
    * Jose\Component\Checker\InvalidHeaderException
    
5. To get the information of the user associated with the token, use:
    ```php
    $uls->getInfo();
    ```
    This will return an array of the decoded JSON from ULS.  Details of the array can be found in
    the VATUSA Technical Manual M1022 at https://www.vatusa.net.
    
## License

Released under the GNU Public License 3.0, see [LICENSE](LICENSE).
