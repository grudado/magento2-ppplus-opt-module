<?php

namespace Paypal\PaypalPlusBrasil\Gateway\Webhook\Handlers;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class Cancel extends AbstractHandler
{

    /**
     * @param Order|string $order
     *
     * @return void
     * @throws LocalizedException
     *
     */
    public function handle($order, $saleId)
    {
        try {
            if (is_string($order)) {
                $order = $this->getOrder($order);
            }

            //tratamento para atualizar pedido quando o mesmo estiver em pending
            if ($order->getState() === Order::STATE_PAYMENT_REVIEW) {
                $order->setState(Order::STATE_PROCESSING);
            }
            if (!$this->cancel($order)) {
                throw new LocalizedException(__("Can't cancel order %1!", $order->getIncrementId()));
            }
            $order->addCommentToStatusHistory(
                __('Webhook handled. Order canceled! Payment Sale ID %1 denied', $saleId)
            );
            $this->orderRepository->save($order);
        } catch (\Exception $exception) {
            $this->logger->error(__('Webhook cancel error!'));
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function cancel(&$order)
    {
        if ($order->canCancel()) {
            $order->cancel();
            return true;
        }

        return false;
    }
}
