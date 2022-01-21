<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Response;

use Laminas\Json\Json;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class RefundHandler implements HandlerInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = SubjectReader::readPayment($handlingSubject);
        $payment = $payment->getPayment();

        $this->logger->info('REFUND HANDLER: ' . Json::encode($response, true));

        try {
            $refundResult = $response['response'];
            $paymentRequest = $response['payment_request'] ?? [];
            $refunds = $payment->getAdditionalInformation('refund') ?: [];
            $payment->setAdditionalInformation('refund', array_merge($refunds, $refundResult));

            if ($paymentRequest) {
                $relatedResources = $paymentRequest['transactions'][0]['related_resources'][0];
                $payment->setAdditionalInformation('payment_id', $paymentRequest['id']);
                $payment->setAdditionalInformation('links', $paymentRequest['links']);
                $payment->setAdditionalInformation('related_resources', $relatedResources);
            }

            $payment->setIsTransactionPending(false);
            $payment->setIsTransactionClosed(true);
            $payment->setShouldCloseParentTransaction(true);

            $payment->getOrder()->setCanSendNewEmailFlag(true);
        } catch (\Exception $e) {
            $this->logger->info('REFUND HANDLER ERROR', [$e->getMessage()]);
        }
    }
}
