<?php

namespace Paypal\PaypalPlusBrasil\Gateway\Webhook\Handlers;

use Paypal\PaypalPlusBrasil\Logger\Logger;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\CreditmemoManagementInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class Refund extends AbstractHandler
{
    /**
     * @var CreditmemoLoader
     */
    private $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    private $creditmemoSender;

    /**
     * @var CreditmemoManagementInterfaceFactory
     */
    private $creditmemoFactory;

    /**
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param CreditmemoManagementInterfaceFactory $creditmemoManagementInterfaceFactory
     */
    public function __construct(
        Logger $logger,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        CreditmemoManagementInterfaceFactory $creditmemoManagementInterfaceFactory
    ) {
        parent::__construct($logger, $orderRepository, $searchCriteriaBuilder);
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->creditmemoFactory = $creditmemoManagementInterfaceFactory;
    }

    /**
     * @param string|Order $order
     * @param string $description
     * @param float amount
     * @throws LocalizedException
     */
    public function handle($order, $description, $amount)
    {
        //TODO tratar refund parcial
        if (is_string($order)) {
            $order = $this->getOrder($order);
        }

        if ($order->getTotalOnlineRefunded() == $order->getTotalPaid()) {
            throw new LocalizedException(__("Order already refunded!"));
        }

        $lastCreditmemo = $order->getCreditmemosCollection()->getLastItem();

        if ($lastCreditmemo && (float)$lastCreditmemo->getGrandTotal() === $amount) {
            throw new LocalizedException(__("Creditmemo already done!"));
        }

        $creditMemoData = [];
        $creditMemoData['shipping_amount'] = 0;
        $creditMemoData['adjustment_positive'] = 0;
        $creditMemoData['adjustment_negative'] = 0;
        $creditMemoData['comment_text'] = $description ?: __('Webhook handled. Creditmemo created!');
        $creditMemoData['send_email'] = 1;
        $creditMemoData['refund_customerbalance_return_enable'] = 0;
        $creditMemoData['is_webhook'] = true;

        $itemsToCredit = [];
        foreach ($order->getItems() as $item) {
            $itemsToCredit[$item->getItemId()] = ['qty' => (int)$item->getQtyInvoiced()];
        }
        $creditMemoData['items'] = $itemsToCredit;

        try {
            $this->creditmemoLoader->setOrderId($order->getEntityId());
            $this->creditmemoLoader->setCreditmemo($creditMemoData);
            $invoice = $order->getInvoiceCollection()->getFirstItem();
            if ($invoice) {
                $this->creditmemoLoader->setInvoiceId($invoice->getEntityId());
            }

            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new LocalizedException(__('The credit memo\'s total must be positive.'));
                }

                if (!empty($creditMemoData['comment_text'])) {
                    $creditmemo->addComment($creditMemoData['comment_text']);

                    $creditmemo->setCustomerNote(__('Creditmemo created!'));
                    $creditmemo->setCustomerNoteNotify(true);
                }

                /** @var CreditmemoManagementInterface $creditmemoManagement */
                $creditmemoManagement = $this->creditmemoFactory->create();
                $creditmemo->getOrder()->setCustomerNoteNotify(false);
                $extensionAttribute = $creditmemo->getExtensionAttributes();
                $extensionAttribute->setIsWebhook(true);
                $creditmemo->setExtensionAttributes($extensionAttribute);
                $creditmemoManagement->refund($creditmemo, false);

                $this->creditmemoSender->send($creditmemo);
                //reload order
                $order = $this->getOrder($order->getIncrementId());
                $order->setState(Order::STATE_CLOSED)->setStatus(Order::STATE_CLOSED);
                $this->orderRepository->save($order);
            }
        } catch (\Exception $exception) {
            $this->logger->error(__('Webhook refund error!'));
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }
}
