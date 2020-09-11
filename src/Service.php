<?php
namespace App;

use Dimafe6\BankID\Service\BankIDService;
use Dimafe6\BankID\Model\CollectResponse;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;


class Service
{
    private const BANK_ID_API_LIVE_ENDPOINT = 'https://appapi2.bankid.com/rp/v5/';

    private $client;

    public function __construct($bankIdCert) {
        $this->client = new BankIDService(
            self::BANK_ID_API_LIVE_ENDPOINT,
            '127.0.0.1',
            [
                'verify' => false,
                'cert'   => $bankIdCert,
            ]
        );
    }

    /**
     * @param string $personalNumber
     *
     * @return string
     * @throws ServiceException
     */
    public function authRequest(string $personalNumber): string
    {
        try {
            $response = $this->client->getAuthResponse($personalNumber);
        } catch (ClientException $e) {
            throw new ServiceException(json_decode($e->getResponse()->getBody(), true)['details']);
        } catch (RequestException $e) {
            throw new ServiceException($e->getMessage() . PHP_EOL . 'Did you enter wrong key pass perhaps?');
        }

        if (!isset($response->orderRef)) {
            throw new ServiceException('Sign request failed');
        }

        return $response->orderRef;
    }


    /**
     * @param $orderRef
     *
     * @return bool
     * @throws ServiceException
     */
    public function collectPoll(string $orderRef): bool
    {
        $retries = 0;

        do {

            try {
                $collectResponse = $this->client->collectResponse($orderRef);
            } catch (RequestException $e) {
                throw new ServiceException($e->getMessage());
            }

            // Cancel request after 20 sec
            if (($retries++ > 10) && $this->client->cancelOrder($orderRef)) {
                throw new ServiceException('Sign request timeout');
            }

            echo '.';

            sleep(2);

        } while ($collectResponse->status === CollectResponse::STATUS_PENDING);

        if ($collectResponse->status === CollectResponse::STATUS_FAILED) {
            throw new ServiceException('Sign failed');
        }

        return true;
    }
}
