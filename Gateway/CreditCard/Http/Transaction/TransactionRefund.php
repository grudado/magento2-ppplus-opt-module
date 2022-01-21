<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Http\Transaction;

use Laminas\Json\Json;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Paypal\PaypalPlusBrasil\Gateway\Http\Client;
use Paypal\PaypalPlusBrasil\Logger\Logger;

class TransactionRefund implements ClientInterface
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

        $this->logger->info('Transaction Refund', [$request]);

        $response = [
            'success' => true,
            'response' => [],
            'msg' => ''
        ];

        try {
            if ($request['is_webhook']) {
                $refunds = $this->getRefund($request['payment_id'], $request['refund_ids']);
            } else {
                $refundResponse = $this->client->refundSale($request['sale_id'], $request['amount']);
                if ($refundResponse->isSuccessful()) {
                    $refunds = [Json::decode($refundResponse->getBody(), true)];
                } else {
                    $body = Json::decode($refundResponse->getBody(), true);
                    if (isset($body['name']) && $body['name'] === 'TRANSACTION_ALREADY_REFUNDED') {
                        $msg = __("Transaction Already Refunded");
                    }
                    throw new \Exception($msg ?: $refundResponse->getBody());
                }
            }

            $paymentRequest = $this->client->getPaymentRequest($request['payment_id']);
            if ($paymentRequest->isSuccessful()) {
                $response['payment_request'] = Json::decode($paymentRequest->getBody(), true);
            }

            $response['response'] = $refunds;
        } catch (\Exception $e) {
            $this->logger->info('Transaction Authorization ERROR', [$e->getMessage()]);
            $response['success'] = false;
            $response['msg'] = $e->getMessage();
        }

        $this->logger->info("Transaction Refund Response" . Json::encode($response));

        return $response;
    }

    private function getRefund($paymentId, $refundIds)
    {
        try {
            $paymentRequest = $this->client->getPaymentRequest($paymentId);
            if ($paymentRequest->isSuccessful()) {
                $body = Json::decode($paymentRequest->getBody(), true);
                $relatedResources = $body['transactions'][0]['related_resources'];
                $refunds = [];
                foreach ($relatedResources as $resource) {
                    if (isset($resource['refund']) && !in_array($resource['refund']['id'], $refundIds)) {
                        $refunds[] = $resource['refund'];
                    }
                }
                if ($refunds) {
                    $refundResponses = [];
                    foreach ($refunds as $refund) {
                        try {
                            $response = $this->client->getRefund($refund['id']);
                            if ($response->isSuccessful()) {
                                $refundResponses[] = Json::decode($response->getBody(), true);
                            }
                        } catch (\Exception $exception) {
                            //do nothing
                        }
                    }
                    return $refundResponses;
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error(__("Get Payment Request Error"));
            $this->logger->error($exception->getMessage());
        }

        return [];
    }

}
