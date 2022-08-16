<?php

use App\BankIDToolbox;
use App\BankIDToolboxException;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    die('Usage: ' . $argv[0] . ' [full personal number]');
}

if (file_exists(BankIDToolbox::PEM_BUNDLE) && !BankIDToolbox::validAsciiCert(BankIDToolbox::PEM_BUNDLE)) {
    echo 'Your chained cert needs to be of type ASCII (PEM), not a binary (DER)' . PHP_EOL;
    exit();
}

$bankIDToolbox = new App\BankIDToolbox();

//Try to create the pem chain if not found
if (!file_exists(BankIDToolbox::PEM_BUNDLE)) {
    try {
        $bankIDToolbox->createPemChain();
    } catch (BankIDToolboxException $e) {
        exit($e->getMessage() . PHP_EOL);
    }
}

if (!file_exists(BankIDToolbox::PEM_BUNDLE)) {
    exit('No cert found');
}

try {
    $orderRef = $bankIDToolbox->authRequest(str_replace('-', '', $argv[1]));
    echo 'Open your bank-id and sign the auth request' . PHP_EOL;
} catch (BankIDToolboxException $e) {
    exit($e->getMessage() . PHP_EOL);
}

try {
    $bankIDToolbox->collectPoll($orderRef);
    echo '..Success!' . PHP_EOL;
} catch (BankIDToolboxException $e) {
    exit('..' . $e->getMessage() . PHP_EOL);
}
