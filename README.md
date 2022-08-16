BankID Toolbox
=======

A simple service wrapper for testing your real Bank-id cert bundle to make sure it's valid.
This library implements BankID API V5.

This docker setup includes a PHP container with Composer, no local requirements needed.

## Requirements

* Docker
* Your PEM chain bundle or a key and the cert from your bank (or you can create a test cert)

## Getting started

For test environments, you should not order a certificate. Instructions for obtaining an RP certificate
for testing are available for download at https://www.bankid.com/utvecklare/rp-info, and in the
document "BankID Relying Party Guidelines" you will find detailed instructions.

Certificate for production use: 
* Once the agreement for the service is signed with your bank, it is time to produce a certificate. In order for your bank to produce a certificate, you must first create a so called Certificate Request file.
* Start by downloading the BankID Keygen software here: https://www.bankid.com/bankid-keygen
* Follow the instructions (in the downloadable PDF) that describes how to install JCE and how to use the downloaded BankID Keygen software to create a Certificate Request file.
* Email the Certificate Request file to your bank. (Important - Only persons with authority to order a certificate may submit the order).

## Usage

If you already have a cert bundle (PEM chain) then add it to /cert folder, rename it to bankid.pem.
If you only have a key and a cert, put them in the /cert folder,
make sure the file ending is .key and .cer (or .crt) and the PEM will be created after validation.

``` bash
$ docker-compose up -d
$ php bin/auth.php [your full personal number]
```
