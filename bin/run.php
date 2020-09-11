<?php

use App\ServiceException;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    die('Usage: ' . $argv[0] . ' [full personal number]');
}

$certFile = glob('cert/*.pem')[0] ?? null;

$testService = new App\Service($certFile);

try {
    $orderRef = $testService->authRequest($argv[1]);
} catch (ServiceException $e) {
    echo $e->getMessage() . PHP_EOL;
    exit();
}

echo 'Open your bank-id and sign the auth request' . PHP_EOL;

try {
    $testService->collectPoll($orderRef);
} catch (ServiceException $e) {
    echo $e->getMessage() . PHP_EOL;
    exit();
}

echo 'Success!';
