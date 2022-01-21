<?php

namespace Paypal\PaypalPlusBrasil\Plugin\Sales\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CommandInterface as BaseCommandInterface;
use Paypal\PaypalPlusBrasil\Gateway\GeneralConfig;
use Paypal\PaypalPlusBrasil\Model\Ui\CreditCard\ConfigProvider;

class CaptureCommandPlugin
{
    /**
     * @var GeneralConfig
     */
    private $generalConfig;

    /**
     * @param GeneralConfig $generalConfig
     */
    public function __construct(
        GeneralConfig $generalConfig
    ) {
        $this->generalConfig = $generalConfig;
    }

    /**
     * @param BaseCommandInterface $subject
     * @param \Closure $proceed
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @param OrderInterface $order
     */
    public function aroundExecute(
        BaseCommandInterface $subject,
        \Closure $proceed,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ) {
        $result = $proceed($payment, $amount, $order);

        if (!$this->getMethodConfig($payment->getMethod())) {
            return $result;
        }

        $orderState = $order->getState();
        $orderStatus = $order->getStatus();
        if ($orderState === Order::STATE_PAYMENT_REVIEW) {
            $orderStatus = $this->generalConfig->getOrderPendingStatus();
        } elseif ($orderState === Order::STATE_PROCESSING) {
            $orderStatus = $this->generalConfig->getOrderCompleteStatus();
        }

        $order->setState($orderState)->setStatus($orderStatus);

        return $result;
    }

    /**
     * @param string $method
     */
    private function getMethodConfig($method)
    {
        $moduleMethods = [
            ConfigProvider::CODE
        ];

        return in_array($method, $moduleMethods);
    }
}
