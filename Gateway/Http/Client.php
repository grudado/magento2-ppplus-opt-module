<?php

namespace Paypal\PaypalPlusBrasil\Gateway\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientFactory as GuzzleClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\ResponseFactory;
use Laminas\Json\Json;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Paypal\PaypalPlusBrasil\Gateway\GeneralConfig;
use Paypal\PaypalPlusBrasil\Logger\Logger;
use Paypal\PaypalPlusBrasil\Traits\FormatFields;
use Psr\Http\Message\ResponseInterface;

class Client
{
    use FormatFields;

    const PATH_OAUTH = 'v1/oauth2/token';
    const PATH_PAYMENT_REQUEST = 'v1/payments/payment';
    const PATH_PAYMENT_REQUEST_EXECUTE = self::PATH_PAYMENT_REQUEST . "/[payment_id]/execute";
    const PATH_PAYMENT_REQUEST_PATCH = self::PATH_PAYMENT_REQUEST . "/[payment_id]";
    const PATH_PAYMENT_REQUEST_GET = self::PATH_PAYMENT_REQUEST . "/[payment_id]";
    const PATH_NOTIFICATIONS_WEBHOOKS = 'v1/notifications/webhooks';
    const PATH_PAYMENT_REFUND = "v1/payments/sale/[sale_id]/refund";
    const PATH_PAYMENT_REFUND_GET = "v1/payments/refund/[refund_id]";
    const PATH_PAYMENT_SALE_GET = 'v1/payments/sale/[sale_id]';

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var ZendClientFactory
     */
    protected $clientFactory;
    /**
     * @var GeneralConfig
     */
    protected $generalConfig;
    /**
     * @var GuzzleClientFactory
     */
    protected $guzzleClientFactory;

    /**
     * @param Logger $logger
     * @param ZendClientFactory $clientFactory
     * @param GeneralConfig $generalConfig
     * @param GuzzleClientFactory $guzzleClientFactory
     */
    public function __construct(
        Logger $logger,
        ZendClientFactory $clientFactory,
        GeneralConfig $generalConfig,
        GuzzleClientFactory $guzzleClientFactory
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->generalConfig = $generalConfig;
        $this->guzzleClientFactory = $guzzleClientFactory;
    }

    private function getAccessToken()
    {
        $token = $this->generalConfig->getToken();
        if ($token) {
            return $token;
        }
        $clientId = $this->generalConfig->getClientId();
        $secret = $this->generalConfig->getSecret();
        $base64 = base64_encode(sprintf("%s:%s", $clientId, $secret));
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();

        $client->setUri($this->generalConfig->getUrl() . self::PATH_OAUTH);
        $client->setMethod(ZendClient::POST);
        $client->setHeaders(
            [
                'Authorization' => "Basic $base64",
                'Content-Type' => 'application/json',
            ]
        );
        $client->setParameterPost(
            [
                'grant_type' => 'client_credentials',
                'response_type' => 'token'
            ]
        );
        try {
            $response = $client->request();
            if ($response->isSuccessful()) {
                $response = Json::decode($response->getBody(), true);
                $token = $response['token_type'] . ' ' . $response['access_token'];
                $this->generalConfig->setToken($token, $response['expires_in']);
            } else {
                throw new \Exception($response->getMessage());
            }
        } catch (\Exception $exception) {
            $this->logger->error(__("Error on generate access token"));
            $this->logger->error($exception->getMessage());
            throw $exception;
        }

        return $token;
    }

    private function getHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => $this->getAccessToken(),
            'Accept' => '*/*',
            'PayPal-Partner-Attribution-Id' => 'DigitalHub_Ecom'
        ];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function request($method, $uri, $params = [])
    {
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();

        $client->setMethod($method);
        $client->setUri($this->generalConfig->getUrl() . $uri);
        $client->setHeaders($this->getHeader());

        if ($params) {
            switch ($method) {
                case ZendClient::POST:
                case ZendClient::PUT:
                case ZendClient::DELETE:
                    $client->setRawData(Json::encode($params));
                    break;
                case ZendClient::PATCH:
                    $client->setConfig(['timeout' => 60]);
                    $client->setParameterPost($params);
                    break;
                case ZendClient::GET:
                    $client->setParameterGet($params);
                    break;
            }
        }

        return $client->request();
    }

    /**
     * @param array $payload
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function createPaymentRequest($payload)
    {
        return $this->request('POST', self::PATH_PAYMENT_REQUEST, $payload);
    }

    /**
     * @param string $payerId
     * @param string $paymentId
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function executePaymentRequest($payerId, $paymentId)
    {
        $uri = self::PATH_PAYMENT_REQUEST_EXECUTE;
        $uri = str_replace('[payment_id]', $paymentId, $uri);
        $payload = ['payer_id' => $payerId];
        return $this->request(ZendClient::POST, $uri, $payload);
    }

    /**
     * @param string $paymentId
     * @param array $payload
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function patchPaymentRequest($paymentId, $payload)
    {
        $uri = self::PATH_PAYMENT_REQUEST_PATCH;
        $uri = str_replace('[payment_id]', $paymentId, $uri);

        return $this->guzzleRequest(ZendClient::PATCH, $uri, $payload);
    }

    /**
     * @param string $paymentId
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getPaymentRequest($paymentId)
    {
        $uri = self::PATH_PAYMENT_REQUEST_GET;
        $uri = str_replace('[payment_id]', $paymentId, $uri);

        return $this->request(ZendClient::GET, $uri);
    }

    /**
     * @param string $saleId
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getPaymentSale($saleId)
    {
        $uri = self::PATH_PAYMENT_SALE_GET;
        $uri = str_replace('[sale_id]', $saleId, $uri);

        return $this->request(ZendClient::GET, $uri);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $payload
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function guzzleRequest($method, $uri, $payload)
    {
        /** @var GuzzleClient $client */
        $client = $this->guzzleClientFactory->create(['config' => ['base_uri' => $this->generalConfig->getUrl()]]);
        $params = [
            'headers' => $this->getHeader(),
            'body' => Json::encode($payload)
        ];

        $response = $client->request($method, $uri, $params);
        $restype = floor($response->getStatusCode() / 100);
        if ($restype == 2 || $restype == 1) {
            return $response;
        }
        throw new \Exception(__("Error on client request. Status Code: %1", $response->getStatusCode()));
    }

    /**
     * @param string $url
     * @param array $types
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function postWebhooksTypes($url, $types)
    {
        $payload['url'] = $url;
        $payload['event_types'] = [];

        foreach ($types as $type) {
            $payload['event_types'][] = ['name' => $type];
        }

        return $this->request(ZendClient::POST, self::PATH_NOTIFICATIONS_WEBHOOKS, $payload);
    }

    /**
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getWebhooks()
    {
        return $this->request(ZendClient::GET, self::PATH_NOTIFICATIONS_WEBHOOKS);
    }

    /**
     * @param $webhookId
     * @param $types
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function updateWebhook($webhookId, $types)
    {
        $uri = self::PATH_NOTIFICATIONS_WEBHOOKS . "/$webhookId";
        $payload = [
            'op' => 'replace',
            'path' => '/event_types',
            'value' => []
        ];
        foreach ($types as $type) {
            $payload['value'][] = ['name' => $type];
        }

        return $this->guzzleRequest(ZendClient::PATCH, $uri, [$payload]);
    }

    /**
     * @param $webhookId
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function deleteWebhook($webhookId)
    {
        $uri = self::PATH_NOTIFICATIONS_WEBHOOKS . "/$webhookId";
        return $this->request(ZendClient::DELETE, $uri);
    }

    /**
     * @param string $refundId
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getRefund($refundId)
    {
        $uri = str_replace('[refund_id]', $refundId, self::PATH_PAYMENT_REFUND_GET);
        return $this->request(ZendClient::GET, $uri);
    }

    public function refundSale($saleId, $amount = null)
    {
        $uri = str_replace('[sale_id]', $saleId, self::PATH_PAYMENT_REFUND);
        $payload = [];
        if ($amount) {
            $payload['amount'] = [
                'total' => $this->convertFloat($amount),
                'currency' => 'BRL'
            ];
        }

        return $this->request(ZendClient::POST, $uri, $payload);
    }

}
