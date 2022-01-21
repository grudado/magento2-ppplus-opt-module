<?php

namespace Paypal\PaypalPlusBrasil\Controller\Adminhtml\Webhook;

use GuzzleHttp\Exception\ClientException;
use Laminas\Json\Json;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Url;
use Paypal\PaypalPlusBrasil\Gateway\GeneralConfig;
use Paypal\PaypalPlusBrasil\Gateway\Http\Client;

class Save extends Action implements ActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Url
     */
    private $url;

    /**
     * @var GeneralConfig
     */
    private $generalConfig;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Client $client
     * @param Url $url
     * @param GeneralConfig $generalConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Client $client,
        Url $url,
        GeneralConfig $generalConfig
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->client = $client;
        $this->url = $url;
        $this->generalConfig = $generalConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $eventTypes = $this->getRequest()->getParam('webhook_event_types') ?: [];
        $webhookUrl = $this->url->getUrl('rest/V1/paypalplusbrasil/webhook/notification');

        $data = [
            'success' => true,
            'message' => '',
        ];

        try {
            $webhookId = $this->generalConfig->getWebhookId();

            if ($webhookId) {
                //se nenhum evento marcado, deleta webhook. já que não da pra salvar um webhook sem eventos
                if (!$eventTypes) {
                    $response = $this->client->deleteWebhook($webhookId);
                    if ($response->isSuccessful()) {
                        $data['message'] = __("Webhook '%1' deleted", $webhookId);
                        $this->generalConfig->setWebhookId('');
                    } elseif ($response->getStatus() === 404) {
                        //se webhook ja deletado do painel, remover do magento tambem
                        $this->generalConfig->setWebhookId('');
                        $data['message'] = __("Webhook '%1' deleted", $webhookId);
                    } else {
                        $data['success'] = false;
                        $data['message'] = $response->getBody();
                    }
                } else {
                    //se webhook com a mesma url ja existe, substituir eventos pelos escolhidos
                    $response = $this->client->updateWebhook($webhookId, $eventTypes);
                    $body = Json::decode((string)$response->getBody(), true);
                    $data['message'] = __("Webhook ID '%1' and URL '%2' updated", $body['id'], $body['url']);
                }
            } else {
                //se webhook nao existe, cria-se um novo
                $data = $this->createWebhook($webhookUrl, $eventTypes);
            }
        } catch (ClientException $clientException) {
            //se erro ao tentar atualizar webhook deletado, cria-se novo
            if ($clientException->getCode() === 404) {
                try {
                    $data = $this->createWebhook($webhookUrl, $eventTypes);
                } catch (\Exception $exception) {
                    $data['success'] = false;
                    $data['message'] = $exception->getMessage();
                }
            } else {
                $data['success'] = false;
                $data['message'] = (string)$clientException->getResponse()->getBody();
            }
        } catch (\Exception $exception) {
            $data['success'] = false;
            $data['message'] = $exception->getMessage();
        }

        $json = $this->jsonFactory->create();

        return $json->setData($data);
    }

    private function createWebhook($webhookUrl, $eventTypes)
    {
        $response = $this->client->postWebhooksTypes($webhookUrl, $eventTypes);
        if ($response->isSuccessful()) {
            $body = Json::decode($response->getBody(), true);
            $data['success'] = true;
            $data['message'] = __("Webhook ID '%1' and URL '%2' created", $body['id'], $body['url']);
            $this->generalConfig->setWebhookId($body['id']);
        } else {
            $data['success'] = false;
            $data['message'] = $response->getBody();
        }
        return $data;
    }

}
