<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Response;

use Paypal\PaypalPlusBrasil\Logger\Logger;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class CaptureHandler implements HandlerInterface
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

        $this->logger->info('CAPTURE HANDLER', [$response]);

        $paymentResult = $response['response'];
        $relatedResources = $paymentResult['transactions'][0]['related_resources'][0];

        try {
            $payment->setAdditionalInformation('related_resources', $relatedResources);

            $payment->setIsTransactionPending(false);
            $payment->setIsTransactionClosed(true);
            $payment->setShouldCloseParentTransaction(true);

            $payment->getOrder()->setCanSendNewEmailFlag(true);
        } catch (\Exception $e) {
            $this->logger->info('CAPTURE HANDLER ERROR', [$e->getMessage()]);
        }
    }
}
