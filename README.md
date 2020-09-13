Bank-ID Test service
=======

A simple service wrapper for testing your real Bank-id cert bundle to make sure it's valid.
This library implements BankID API V5.

This docker setup includes a PHP container with Composer, no local requirements needed.

## Requirements

* PHP 5.6+ or 7.0+
* [curl](http://php.net/manual/en/book.curl.php)

## Usage

Put your cert bundle in the /cert folder, name it how you want as long as the file ending is .pem

``` bash
$ docker-compose up -d
$ php bin/auth.php [your personal number]
```
