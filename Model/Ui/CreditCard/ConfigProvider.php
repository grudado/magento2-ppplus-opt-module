<?php

namespace Paypal\PaypalPlusBrasil\Model\Ui\CreditCard;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;
use Paypal\PaypalPlusBrasil\Gateway\CreditCard\Config\Config;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'paypalplus_brasil_creditcard';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config,
        Repository $assetRepo
    ) {
        $this->config = $config;
        $this->assetRepo = $assetRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $active = (bool)$this->config->isActive();
        $title = $this->config->getTitle();

        return [
            'payment' => [
                self::CODE => [
                    'active' => $active,
                    'title' => $title,
                    'method' => self::CODE,
                    'loader_image_url' => $this->assetRepo->getUrl('images/loader-2.gif')
                ]
            ]
        ];
    }
}
