<?php

namespace Paypal\PaypalPlusBrasil\Controller\Adminhtml\PaymentRequest;

use Laminas\Json\Json;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Paypal\PaypalPlusBrasil\Gateway\Http\Client;
use Paypal\PaypalPlusBrasil\Logger\Logger;

class View extends Action
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context $context
     * @param Client $client
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Client $client,
        JsonFactory $jsonFactory,
        Logger $logger
    ) {
        $this->client = $client;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $paymentId = $this->getRequest()->getParam('payment_id');
        $json = $this->jsonFactory->create();

        try {
            $paymentRequest = $this->client->getPaymentRequest($paymentId);
            $body = Json::decode($paymentRequest->getBody(), true);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $body = [];
        }

        return $json->setData($body);
    }
}
