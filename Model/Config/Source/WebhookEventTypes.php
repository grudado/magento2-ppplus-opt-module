<?php

namespace Paypal\PaypalPlusBrasil\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WebhookEventTypes implements OptionSourceInterface
{
    const PAYMENT_SALE_COMPLETED = 'PAYMENT.SALE.COMPLETED';
    const PAYMENT_SALE_DENIED = 'PAYMENT.SALE.DENIED';
    const PAYMENT_SALE_PENDING = 'PAYMENT.SALE.PENDING';
    const PAYMENT_SALE_REFUNDED = 'PAYMENT.SALE.REFUNDED';
    const RISK_DISPUTE_CREATED = 'RISK.DISPUTE.CREATED';
    const CUSTOMER_DISPUTE_CREATED = 'CUSTOMER.DISPUTE.CREATED';

    private $types = [
        self::PAYMENT_SALE_COMPLETED,
        self::PAYMENT_SALE_DENIED,
        self::PAYMENT_SALE_PENDING,
        self::PAYMENT_SALE_REFUNDED,
        self::RISK_DISPUTE_CREATED,
        self::CUSTOMER_DISPUTE_CREATED
    ];

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->types as $type) {
            $options[] = [
                'value' => $type,
                'label' => $type,
            ];
        }
        return $options;
    }
}
