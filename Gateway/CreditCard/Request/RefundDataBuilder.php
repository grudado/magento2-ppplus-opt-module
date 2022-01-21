<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Paypal\PaypalPlusBrasil\Observer\CreditCard\DataAssignObserver;

class RefundDataBuilder implements BuilderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * PaymentDataBuilder constructor.
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $amount = SubjectReader::readAmount($buildSubject);
        $relatedResources = $payment->getAdditionalInformation('related_resources');

        /** @var Creditmemo $creditmemo */
        $creditmemo = $payment->getCreditmemo();
        $extensionAttribute = $creditmemo->getExtensionAttributes();
        $isWebhook = $extensionAttribute->getIsWebhook();
        $refund = $payment->getAdditionalInformation('refund') ?: [];
        $refundIds = [];

        foreach ($refund as $data) {
            $refundIds[] = $data['id'];
        }

        $this->logger->info('Refund Data Builder');

        return [
            'amount' => $amount,
            'sale_id' => $relatedResources['sale']['id'],
            'payment_id' => $payment->getAdditionalInformation(DataAssignObserver::PAY_ID),
            'is_webhook' => $isWebhook ?: false,
            'refund_ids' => $refundIds
        ];
    }
}
