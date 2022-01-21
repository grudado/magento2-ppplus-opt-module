<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Paypal\PaypalPlusBrasil\Observer\CreditCard\DataAssignObserver;

class BnCodeDataBuilder implements BuilderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        $this->logger->info(__("BNCode Data Builder"));

        return [
            'PayPal-Partner-Attribution-Id' => 'DigitalHub_Ecom'
        ];
    }
}
