<?php

namespace Paypal\PaypalPlusBrasil\Observer\CreditCard;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Paypal\PaypalPlusBrasil\Logger\Logger;

/**
 * Class DataAssignObserver
 */
class DataAssignObserver extends AbstractDataAssignObserver
{

    const PAY_ID = 'pay_id';
    const PAYER_ID = 'payer_id';
    const REMEMBERED_CARDS_TOKEN = 'remembered_cards_token';
    const INSTALLMENTS = 'installments';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::PAY_ID,
        self::PAYER_ID,
        self::REMEMBERED_CARDS_TOKEN,
        self::INSTALLMENTS
    ];

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

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $this->logger->info('CREDITCARD DATA ASSIGN');

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
