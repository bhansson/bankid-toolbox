<?php
namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

class BankIDToolbox
{
    private const BANK_ID_API_LIVE_ENDPOINT = 'https://appapi2.bankid.com/rp/v5.1/';
    private const END_USER_IP = '127.0.0.1';
    public const PEM_BUNDLE = 'cert/bankid.pem';
    public const ROOT_CA = 'cert/root.ca';

    private const STATUS_COMPLETED = 'complete';
    private const STATUS_PENDING = 'pending';
    private const STATUS_FAILED = 'failed';

    /** @var Client client */
    private $client;

    private $keyPassword;

    public function __construct() {
        $this->client = $this->guzzleFactory();
    }

    private function guzzleFactory(): Client
    {
        $options['base_uri']    = self::BANK_ID_API_LIVE_ENDPOINT;
        $options['json']        = true;
        $options['verify']      = true;
        $options['curl']        = ['CURLOPT_CAINFO' => 'cert/root.ca'];
        $options['cert']        = empty($this->keyPassword)
                                ? self::PEM_BUNDLE
                                : [self::PEM_BUNDLE, $this->keyPassword];

        return new Client($options);
    }

    /**
     * @param string $personalNumber
     *
     * @return string|null
     * @throws BankIDToolboxException
     */
    public function authRequest(string $personalNumber): ?string
    {
        $parameters = [
            'personalNumber' => $personalNumber,
            'endUserIp'      => self::END_USER_IP,
            'requirement'    => [
                'allowFingerprint' => true,
            ],
        ];

        try {
            $response = $this->client->post('auth', ['json' => $parameters]);
            $responseBody  = json_decode($response->getBody()->getContents(), false);
        } catch (ClientException $e) {
            throw new BankIDToolboxException(json_decode($e->getResponse()->getBody(), true)['details']);
        } catch (RequestException $e) {
            throw new BankIDToolboxException($e->getMessage() . PHP_EOL . 'Did you enter wrong key pass perhaps?');
        }

        if (!isset($responseBody->orderRef)) {
            throw new BankIDToolboxException('Auth request failed');
        }

        return $responseBody->orderRef;
    }

    /**
     * @param $orderRef
     *
     * @return bool
     * @throws BankIDToolboxException
     */
    public function collectPoll(string $orderRef): bool
    {
        $waitSec = 20;

        do {
            echo $waitSec-- . '.';
            sleep(1);

            // Only make the request every 2 sec
            if ($waitSec % 2) {
                $responseBody['status'] = self::STATUS_PENDING;
                continue;
            }

            try {
                $response = $this->client->post('collect', ['json' => ['orderRef' => $orderRef]]);
                $responseBody = json_decode($response->getBody()->getContents(), true);
            } catch (RequestException $e) {
                throw new BankIDToolboxException($e->getMessage());
            }

            // Cancel request?
            if (!$waitSec && $this->cancelAuth($orderRef)) {
                throw new BankIDToolboxException('Sign request timeout');
            }

        } while ($responseBody['status'] === self::STATUS_PENDING);

        if ($responseBody['status'] === self::STATUS_FAILED) {
            throw new BankIDToolboxException('Sign failed');
        }

        return $responseBody['status'] === self::STATUS_COMPLETED;
    }

    /**
     * @param $orderRef
     *
     * @return bool
     */
    public function cancelAuth($orderRef): bool
    {
        return 200 === $this->client->post('cancel', ['json' => ['orderRef' => $orderRef]])->getStatusCode();
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public static function validAsciiCert($file): bool
    {
        if (!file_exists($file)) {
            echo 'File not found';
            return false;
        }

        $pemContent = file_get_contents($file);
        return !(strpos($pemContent, '-BEGIN CERTIFICATE-') === false);
    }

    /**
     * @return bool
     * @throws BankIDToolboxException
     */
    public function createPemChain(): bool
    {
        $keyFile = glob('cert/*.key')[0] ?? null;
        $certFile = glob('cert/*.cer')[0] ?? glob('cert/*.crt')[0] ?? null;

        if (!isset($keyFile, $certFile)) {
            throw new BankIDToolboxException('No files found for creating cert bundle.');
        }

        if (($certFile !== null) && !self::validAsciiCert($certFile)) {
            throw new BankIDToolboxException('Failed to create PEM cert');
        }

        if (!openssl_x509_check_private_key(file_get_contents($certFile), file_get_contents($keyFile))) {

            // Key may be password protected, ask for password
            $this->keyPassword = trim(readline('Enter KEY pass phrase or hit enter if no password: '));

            // Set new client if we got a password
            $this->client = $this->guzzleFactory();

            if ($this->keyPassword !== '' && !openssl_x509_check_private_key(file_get_contents($certFile), [file_get_contents($keyFile), $this->keyPassword])) {
                throw new BankIDToolboxException('Invalid key or cert');
            }
        }

        if (!self::concatFiles([$keyFile, $certFile, self::ROOT_CA], self::PEM_BUNDLE)) {
            throw new BankIDToolboxException('Failed to create PEM cert');
        }

        return true;
    }

    /**
     * @param $inputFiles
     * @param $outputFile
     *
     * @return bool
     */
    public static function concatFiles($inputFiles, $outputFile): bool
    {
        if (empty($inputFiles)) {
            return false;
        }

        foreach ($inputFiles as $file) {
            $content = rtrim(file_get_contents($file));
            $content .= PHP_EOL;
            file_put_contents($outputFile, $content, FILE_APPEND);
        }
        return true;
    }
}
