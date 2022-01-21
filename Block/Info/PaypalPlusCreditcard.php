<?php

namespace Paypal\PaypalPlusBrasil\Block\Info;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Block\Info;

class PaypalPlusCreditcard extends Info
{
    /**
     * @var string
     */
    protected $_template = 'Paypal_PaypalPlusBrasil::info/paypalplus_creditcard.phtml';

    private $pricingHelper;

    public function __construct(
        Template\Context $context,
        Data $pricingHelper,
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $data);
    }

    public function formatCurrency($value)
    {
        return $this->pricingHelper->currency($value, true, false);
    }
}
