<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Paypal\PaypalPlusBrasil\Model\Ui\CreditCard\ConfigProvider;
use Paypal\PaypalPlusBrasil\Observer\CreditCard\DataAssignObserver;
use Magento\Framework\Event\Manager;

class PayerDataBuilder implements BuilderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @param Logger $logger
     * @param Manager $eventManager
     */
    public function __construct(
        Logger $logger,
        Manager $eventManager
    ) {
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }

    public function build(array $buildSubject)
    {
        $this->logger->info(__("Payer Data Builder"));

        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $paymentInfo = $paymentDataObject->getPayment();
        /** @var Order $order */
        $order = $paymentInfo->getOrder();

        if ($paymentInfo->getAdditionalInformation(DataAssignObserver::REMEMBERED_CARDS_TOKEN)
            && $order->getCustomerId()
        ) {
            $this->eventManager->dispatch(
                ConfigProvider::CODE . '_remembered_card_token',
                [
                    'token' => $paymentInfo->getAdditionalInformation(DataAssignObserver::REMEMBERED_CARDS_TOKEN),
                    'customer_id' => $order->getCustomerId()
                ]
            );
        }

        return [
            'payer_id' => $paymentInfo->getAdditionalInformation(DataAssignObserver::PAYER_ID),
            'payment_id' => $paymentInfo->getAdditionalInformation(DataAssignObserver::PAY_ID),
            'increment_id' => $order->getIncrementId(),
            'description' => "Order " . $order->getIncrementId() . " from " . $order->getStore()->getName()
        ];
    }
}
