<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Config;

use Paypal\PaypalPlusBrasil\Model\Ui\CreditCard\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;

class Config extends GatewayConfig
{
    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    const KEY_ENABLE_INSTALLMENTS = 'enable_installments';
    const KEY_PAYMENT_ACTION = 'payment_action';
    const KEY_ENABLE_SAVE_CREDIT_CARD = 'enable_save_credit_card';
    const KEY_IFRAME_HEIGHT = 'iframe_height';
    const KEY_SORT_ORDER = 'sort_order';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = ConfigProvider::CODE,
        $pathPattern = GatewayConfig::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * @return int
     */
    public function isActive()
    {
        return $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    /**
     * @return boolean
     */
    public function getEnableInstallments()
    {
        return (boolean)$this->getValue(self::KEY_ENABLE_INSTALLMENTS);
    }


    /**
     * @return string
     */
    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    /**
     * @return int
     */
    public function getEnableSaveCreditCard()
    {
        return $this->getValue(self::KEY_ENABLE_SAVE_CREDIT_CARD);
    }

    /**
     * @return string
     */
    public function getIframeHeight()
    {
        return (string)$this->getValue(self::KEY_IFRAME_HEIGHT);
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getValue(self::KEY_SORT_ORDER);
    }

}
