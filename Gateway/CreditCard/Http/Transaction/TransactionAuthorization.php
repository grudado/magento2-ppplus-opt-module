<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Http\Transaction;

use Laminas\Json\Json;
use Paypal\PaypalPlusBrasil\Gateway\Http\Client;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransactionAuthorization
 */
class TransactionAuthorization implements ClientInterface
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

        $this->logger->info('Transaction Authorization', [$request]);

        $response = [
            'success' => true,
            'response' => [],
            'msg' => ''
        ];

        try {
            //adiciona order_increment_id ao payment_request e edita description
            $patch = $this->editPaymentRequest(
                $request['payment_id'],
                $request['description'],
                $request['increment_id']
            );
            $paymentRequest = $this->client->executePaymentRequest($request['payer_id'], $request['payment_id']);
            if ($paymentRequest->isSuccessful()) {
                $response['response'] = Json::decode($paymentRequest->getBody(), true);
            }
        } catch (\Exception $e) {
            $this->logger->info('Transaction Authorization ERROR', [$e->getMessage()]);
            $response['success'] = false;
            $response['msg'] = $e->getMessage();
        }

        $this->logger->info("Transaction Authorization Response" . Json::encode($response));

        return $response;
    }

    /**
     * @param string $paymentId
     * @param string $description
     * @param string $incrementId
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function editPaymentRequest($paymentId, $description, $incrementId)
    {
        $payload = [
            [
                'op' => 'replace',
                'path' => '/transactions/0/description',
                'value' => $description
            ],
            [
                'op' => 'add',
                'path' => '/transactions/0/invoice_number',
                'value' => $incrementId
            ]
        ];

        return $this->client->patchPaymentRequest($paymentId, $payload);
    }
}
