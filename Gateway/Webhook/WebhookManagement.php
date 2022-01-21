<?php

namespace Paypal\PaypalPlusBrasil\Gateway\Webhook;

use Laminas\Json\Json;
use Paypal\PaypalPlusBrasil\Api\Webhook\WebhookManagementInterface;
use Paypal\PaypalPlusBrasil\Gateway\GeneralConfig;
use Paypal\PaypalPlusBrasil\Gateway\Webhook\Handlers\Cancel;
use Paypal\PaypalPlusBrasil\Gateway\Webhook\Handlers\Invoice;
use Paypal\PaypalPlusBrasil\Gateway\Webhook\Handlers\Refund;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Paypal\PaypalPlusBrasil\Model\Config\Source\WebhookEventTypes;

class WebhookManagement implements WebhookManagementInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var GeneralConfig
     */
    private $generalConfig;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @var Refund
     */
    private $refund;

    /**
     * @var Cancel
     */
    private $cancel;

    /**
     * @param Logger $logger
     * @param GeneralConfig $generalConfig
     * @param Invoice $invoice
     * @param Refund $refund
     * @param Cancel $cancel
     */
    public function __construct(
        Logger $logger,
        GeneralConfig $generalConfig,
        Invoice $invoice,
        Refund $refund,
        Cancel $cancel
    ) {
        $this->logger = $logger;
        $this->generalConfig = $generalConfig;
        $this->invoice = $invoice;
        $this->refund = $refund;
        $this->cancel = $cancel;
    }

    /**
     * @inheritDoc
     */
    public function handle($id, $create_time, $resource_type, $event_version, $event_type, $summary, $resource, $links)
    {
        try {
            $this->logger->info(__("Receiving webhook event $event_type"));
            $this->logger->info("Resource: " . Json::encode($resource));
            if ($this->isEventTypeAllowed($event_type)) {
                switch ($event_type) {
                    case WebhookEventTypes::PAYMENT_SALE_COMPLETED:
                    case $this->generalConfig->getCreateInvoiceByWebhook():
                        $this->logger->info(
                            __("Handling Order %1 Event PAYMENT.SALE.COMPLETED", $resource['invoice_number'])
                        );
                        $this->invoice->handle($resource['invoice_number']);
                        break;
                    case WebhookEventTypes::PAYMENT_SALE_PENDING:
                        break;
                    case WebhookEventTypes::PAYMENT_SALE_REFUNDED:
                        $this->logger->info(
                            __("Handling Order %1 Event PAYMENT.SALE.REFUNDED", $resource['invoice_number'])
                        );
                        $this->refund->handle(
                            $resource['invoice_number'],
                            ($resource['description'] ?? ''),
                            (float)$resource['amount']['total']
                        );
                        $this->logger->info(__("Creditmemo created! Order {$resource['invoice_number']}"));
                        break;
                    case WebhookEventTypes::PAYMENT_SALE_DENIED:
                        $this->logger->info(
                            __("Handling Order %1 Event PAYMENT.SALE.DENIED", $resource['invoice_number'])
                        );
                        $this->cancel->handle($resource['invoice_number'], $resource['id']);
                        break;
                    case WebhookEventTypes::RISK_DISPUTE_CREATED:
                        break;
                    case WebhookEventTypes::CUSTOMER_DISPUTE_CREATED:
                        break;

                }
                $this->logger->info(__("Webhook event type $event_type handled!"));
            } else {
                $this->logger->info(__("Webhook event type $event_type ignored!"));
            }
        } catch (\Exception $exception) {
            $this->logger->error(__("Webhook Event %1 Handler ERROR!", $event_type));
            $this->logger->error(__($exception->getMessage()));
            return false;
        }

        return true;
    }

    /**
     * @param string $eventType
     * @return bool
     */
    private function isEventTypeAllowed($eventType)
    {
        $types = explode(',', $this->generalConfig->getWebhookEventTypes());
        return in_array($eventType, $types);
    }
}
