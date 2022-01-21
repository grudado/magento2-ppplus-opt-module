<?php

namespace Paypal\PaypalPlusBrasil\Observer\CreditCard;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Paypal\PaypalPlusBrasil\Gateway\GeneralConfig;
use Paypal\PaypalPlusBrasil\Gateway\Webhook\Handlers\Invoice;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Paypal\PaypalPlusBrasil\Model\Ui\CreditCard\ConfigProvider;

class CreateInvoiceObserver implements ObserverInterface
{
    /**
     * @var Invoice
     */
    private $invoice;
    /**
     * @var GeneralConfig
     */
    private $generalConfig;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Invoice $invoice
     * @param GeneralConfig $generalConfig
     * @param Logger $logger
     */
    public function __construct(
        Invoice $invoice,
        GeneralConfig $generalConfig,
        Logger $logger
    ) {
        $this->invoice = $invoice;
        $this->generalConfig = $generalConfig;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');
        $payment = $order->getPayment();
        try {
            if (!$this->generalConfig->getCreateInvoiceByWebhook() && $payment->getMethod() === ConfigProvider::CODE) {
                $additional = $payment->getAdditionalInformation();
                $state = '';
                if (isset($additional['related_resources'])) {
                    $state = $additional['related_resources']['sale']['state'];
                }
                if ($state !== 'completed') {
                    return;
                }
                $this->invoice->handle($order);
            }
        } catch (\Exception $exception) {
            $this->logger->error(__("Cannot create invoice for Order %1 on observer", $order->getIncrementId()));
        }
    }
}
