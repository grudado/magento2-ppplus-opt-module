<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Http\Transaction;

use Laminas\Json\Json;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Paypal\PaypalPlusBrasil\Gateway\Http\Client;
use Paypal\PaypalPlusBrasil\Logger\Logger;

class TransactionCapture implements ClientInterface
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Logger $logger
     * @param Client $client
     */
    public function __construct(
        Logger $logger,
        Client $client
    ) {
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        $this->logger->info('Transaction Capture', [$request]);

        $response = [
            'success' => true,
            'response' => [],
            'msg' => ''
        ];

        try {
            $paymentRequest = $this->client->getPaymentRequest($request['payment_id']);
            if ($paymentRequest->isSuccessful()) {
                $response['response'] = Json::decode($paymentRequest->getBody(), true);
            }
        } catch (\Exception $e) {
            $this->logger->info('Transaction Authorization ERROR', [$e->getMessage()]);
            $response['success'] = false;
            $response['msg'] = $e->getMessage();
        }

        $this->logger->info("Transaction Capture Response" . Json::encode($response));

        return $response;
    }

}
