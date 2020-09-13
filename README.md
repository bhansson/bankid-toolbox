Bank-ID Toolbox
=======

A simple service wrapper for testing your real Bank-id cert bundle to make sure it's valid.
This library implements BankID API V5.

This docker setup includes a PHP container with Composer, no local requirements needed.

## Requirements

* Docker
* Your PEM chain bundle or a key and the cert from your bank

## Usage

If you already have a cert bundle (PEM chain) then add it to /cert folder, rename it to bankid.pem
If you only have a key and a cert, put them in the /cert folder,
make sure the file ending is .key and .cer (or .crt) and the PEM will be created after validation.

``` bash
$ docker-compose up -d
$ php bin/auth.php [your personal number]
```
