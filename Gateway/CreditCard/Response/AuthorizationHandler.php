<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Response;

use Paypal\PaypalPlusBrasil\Logger\Logger;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class AuthorizationHandler implements HandlerInterface
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

        $this->logger->info('AUTHORIZATION HANDLER', [$response]);

        $paymentResult = $response['response'];
        $relatedResources = $paymentResult['transactions'][0]['related_resources'][0];

        try {
            $payment->setAdditionalInformation('payment_id', $paymentResult['id']);
            $payment->setAdditionalInformation('links', $paymentResult['links']);
            $payment->setAdditionalInformation('related_resources', $relatedResources);

            $payment->setCcTransId($relatedResources['sale']['id']);
            $payment->setLastTransId($relatedResources['sale']['id']);
            $payment->setTransactionId($relatedResources['sale']['id']);
            //não deve fechar transacao para captura online
            $payment->setIsTransactionClosed(false);
            $payment->setShouldCloseParentTransaction(false);

            if ($relatedResources['sale']['state'] === 'completed') {
                $payment->setIsTransactionPending(false);
            } else {
                $payment->setIsTransactionPending(true);
            }

            $payment->getOrder()->setCanSendNewEmailFlag(true);
        } catch (\Exception $e) {
            $this->logger->info('AUTHORIZATION HANDLER ERROR', [$e->getMessage()]);
        }
    }
}
