<?php

namespace Paypal\PaypalPlusBrasil\Gateway;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Paypal\PaypalPlusBrasil\Model\Config\Source\IntegrationMode;

class GeneralConfig
{
    const KEY_BASE_PATH = 'paypalplus_brasil/integration/';

    const KEY_MODE = 'mode';
    const KEY_SANDBOX_CLIENT_ID = 'sandbox_client_id';
    const KEY_SANDBOX_SECRET = 'sandbox_secret';
    const KEY_PRODUCTION_CLIENT_ID = 'production_client_id';
    const KEY_PRODUCTION_SECRET = 'production_secret';
    const KEY_ORDER_PENDING_STATUS = 'order_pending_status';
    const KEY_ORDER_COMPLETE_STATUS = 'order_complete_status';
    const KEY_DEBUG = 'debug';
    const KEY_SANDBOX_URL = 'sandbox_url';
    const KEY_PRODUCTION_URL = 'production_url';
    const KEY_ACCESS_TOKEN = 'access_token';
    const KEY_ACCESS_TOKEN_EXPIRES = 'access_token_expires';
    const KEY_WEBHOOK_EVENT_TYPES = 'webhook_event_types';
    const KEY_CREATE_INVOICE_BY_WEBHOOK = 'create_invoice_by_webhook';
    const KEY_WEBHOOK_ID = 'webhook_id';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $writer
     * @param EncryptorInterface $encryptor
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $writer,
        EncryptorInterface $encryptor,
        TypeListInterface $cacheTypeList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->writer = $writer;
        $this->encryptor = $encryptor;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->getValue(self::KEY_MODE);
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        if ($this->getMode() === IntegrationMode::MODE_PRODUCTION) {
            return $this->getProductionClientId();
        } elseif ($this->getMode() === IntegrationMode::MODE_SANDBOX) {
            return $this->getSandboxClientId();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        if ($this->getMode() === IntegrationMode::MODE_PRODUCTION) {
            return $this->encryptor->decrypt($this->getProductionSecret());
        } elseif ($this->getMode() === IntegrationMode::MODE_SANDBOX) {
            return $this->encryptor->decrypt($this->getSandboxSecret());
        }

        return null;
    }

    /**
     * @return string
     */
    public function getOrderPendingStatus()
    {
        return $this->getValue(self::KEY_ORDER_PENDING_STATUS);
    }

    /**
     * @return string
     */
    public function getOrderCompleteStatus()
    {
        return $this->getValue(self::KEY_ORDER_COMPLETE_STATUS);
    }

    /**
     * @return bool
     */
    public function isDebugEnable()
    {
        return (bool)$this->getValue(self::KEY_DEBUG);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->getMode() === IntegrationMode::MODE_PRODUCTION) {
            return $this->getProductionUrl();
        } elseif ($this->getMode() === IntegrationMode::MODE_SANDBOX) {
            return $this->getSandboxUrl();
        }

        return '';
    }

    /**
     * @return string
     */
    private function getSandboxClientId()
    {
        return $this->getValue(self::KEY_SANDBOX_CLIENT_ID);
    }

    /**
     * @return string
     */
    private function getSandboxSecret()
    {
        return $this->getValue(self::KEY_SANDBOX_SECRET);
    }

    /**
     * @return string
     */
    private function getProductionClientId()
    {
        return $this->getValue(self::KEY_PRODUCTION_CLIENT_ID);
    }

    /**
     * @return string
     */
    private function getProductionSecret()
    {
        return $this->getValue(self::KEY_PRODUCTION_SECRET);
    }

    /**
     * @return string
     */
    private function getSandboxUrl()
    {
        return $this->getValue(self::KEY_SANDBOX_URL);
    }

    /**
     * @return string
     */
    private function getProductionUrl()
    {
        return $this->getValue(self::KEY_PRODUCTION_URL);
    }

    /**
     * @return string
     */
    private function getValue($value)
    {
        return $this->scopeConfig->getValue(
            self::KEY_BASE_PATH . $value,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return false|string
     */
    public function getToken()
    {
        $token = $this->getValue(self::KEY_ACCESS_TOKEN);
        if ($token) {
            $expiresin = $this->getValue(self::KEY_ACCESS_TOKEN_EXPIRES);
            $now = new \DateTime();
            $nowTimestamp = $now->getTimestamp();
            if ($nowTimestamp > $expiresin) {
                $token = false;
            }
        }

        return $token;
    }

    /**
     * @param string $token
     * @param string $expiresin
     */
    public function setToken($token, $expiresin)
    {
        //- 5 minutos
        $expiresin -= 300;
        $now = new \DateTime();
        $now->add(new \DateInterval("PT{$expiresin}S"));
        $this->writer->save(self::KEY_BASE_PATH . self::KEY_ACCESS_TOKEN, $token);
        $this->writer->save(self::KEY_BASE_PATH . self::KEY_ACCESS_TOKEN_EXPIRES, $now->getTimestamp());
        //limpa cache após novos valores
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @return string
     */
    public function getWebhookEventTypes()
    {
        return $this->getValue(self::KEY_WEBHOOK_EVENT_TYPES);
    }

    public function getCreateInvoiceByWebhook()
    {
        return $this->getValue(self::KEY_CREATE_INVOICE_BY_WEBHOOK);
    }


    /**
     * @return string
     */
    public function getWebhookId()
    {
        return $this->getValue(self::KEY_WEBHOOK_ID);
    }

    public function setWebhookId($webhookId)
    {
        $this->writer->save(self::KEY_BASE_PATH . self::KEY_WEBHOOK_ID, $webhookId);
        //limpa cache após novos valores
        $this->cacheTypeList->cleanType('config');
    }
}
