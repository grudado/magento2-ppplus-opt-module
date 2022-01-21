<?php

namespace Paypal\PaypalPlusBrasil\Observer\CreditCard;

use Laminas\Json\Json;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Paypal\PaypalPlusBrasil\Gateway\Http\Client;
use Paypal\PaypalPlusBrasil\Logger\Logger;

class UpdateAfterCancel implements ObserverInterface
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Client $client
     * @param Logger $logger
     */
    public function __construct(
        Client $client,
        Logger $logger
    ) {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');
        $payment = $order->getPayment();
        try {
            $additional = $payment->getAdditionalInformation();
            $paymentRequest = $this->client->getPaymentRequest($additional[DataAssignObserver::PAY_ID]);
            if ($paymentRequest->isSuccessful()) {
                $paymentRequestData = Json::decode($paymentRequest->getBody(), true);
                $relatedResources = $paymentRequestData['transactions'][0]['related_resources'][0];
                $payment->setAdditionalInformation('payment_id', $paymentRequestData['id']);
                $payment->setAdditionalInformation('links', $paymentRequestData['links']);
                $payment->setAdditionalInformation('related_resources', $relatedResources);
                $order->setPayment($payment);
            }
        } catch (\Exception $exception) {
            $this->logger->error(__("Error saving additional data after Order %1 cancel", $order->getIncrementId()));
        }
    }
}
