<?php

namespace Paypal\PaypalPlusBrasil\Controller\Checkout;

use Laminas\Json\Json;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory as QuoteIdMaskModelFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Magento\Store\Model\StoreManagerInterface;
use Paypal\PaypalPlusBrasil\Gateway\CreditCard\Config\Config as CreditCardConfig;
use Paypal\PaypalPlusBrasil\Gateway\GeneralConfig;
use Paypal\PaypalPlusBrasil\Gateway\Http\Client;
use Paypal\PaypalPlusBrasil\Model\Mapper\CustomerFieldsMapper;
use Paypal\PaypalPlusBrasil\Traits\FormatFields;

class PaymentRequest implements ActionInterface
{
    use FormatFields;

    /**
     * @var GeneralConfig
     */
    private $generalConfig;
    /**
     * @var CreditCardConfig
     */
    private $creditCardConfig;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var CustomerFieldsMapper
     */
    private $customerFieldsMapper;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var QuoteIdMaskModelFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    /**
     * @param GeneralConfig $generalConfig
     * @param CreditCardConfig $creditCardConfig
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param Client $client
     * @param CustomerFieldsMapper $customerFieldsMapper
     * @param CartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     * @param QuoteIdMaskModelFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResource $quoteIdMaskResource
     */
    public function __construct(
        GeneralConfig $generalConfig,
        CreditCardConfig $creditCardConfig,
        RequestInterface $request,
        JsonFactory $jsonFactory,
        Client $client,
        CustomerFieldsMapper $customerFieldsMapper,
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager,
        QuoteIdMaskModelFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource
    ) {
        $this->generalConfig = $generalConfig;
        $this->creditCardConfig = $creditCardConfig;
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->client = $client;
        $this->customerFieldsMapper = $customerFieldsMapper;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Quote $quote */
        $quote_id = $this->request->getParam('quote_id');
        $quote = $this->getQuote($quote_id);

        $json = $this->jsonFactory->create();
        try {
            $paymentRequestData = $this->createPaymentRequest($quote);
            $paymentRequestData = Json::decode($paymentRequestData, true);

            foreach ($paymentRequestData['links'] as $link) {
                $paymentRequestData[$link['rel']] = $link['href'];
            }
            $data = [
                'success' => true,
                'data' => array_merge($paymentRequestData, $this->addData($quote))
            ];
        } catch (\Exception $exception) {
            $data['success'] = false;
            $data['responseText'] = Json::encode(['message' => $exception->getMessage()]);
        }

        return $json->setData($data);
    }

    private function getQuote($quote_id)
    {
        try {
            $id = (int)$quote_id;
            if (!$id) {
                $quoteIdMask = $this->quoteIdMaskFactory->create();
                $this->quoteIdMaskResource->load($quoteIdMask, $quote_id, 'masked_id');
                $quote_id = $quoteIdMask->getQuoteId();
            }
            return $this->cartRepository->get($quote_id);
        } catch (\Exception $exception) {
            //TODO tratar excecao
            throw $exception;
        }
    }

    /**
     * @param Quote $quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createPaymentRequest($quote)
    {
        $shipping = $quote->getShippingAddress()->getId() ? $quote->getShippingAddress() : null;
        $items = $quote->getAllVisibleItems();
        $store = $this->storeManager->getStore();

        $payload = [
            'intent' => 'sale',
            'payer' => [
                'payment_method' => 'paypal'
            ],
            'redirect_urls' => [
                //TODO alterar URLS
                'return_url' => $store->getUrl('checkout/cart'),
                'cancel_url' => $store->getUrl('checkout/cart')
            ],
            'application_context' => [
                'locale' => 'pt_BR',
                'brand_name' => $store->getName(),
                'shipping_preference' => $shipping ? 'SET_PROVIDED_ADDRESS' : 'NO_SHIPPING'
            ],
            'transactions' => [
                [
                    'amount' => [
                        'currency' => $quote->getBaseCurrencyCode(),
                        'total' => $this->convertFloat($quote->getGrandTotal()),
                        'details' => [
                            'shipping' => $this->convertFloat($shipping ? $shipping->getShippingAmount() : 0),
                            'subtotal' => $this->convertFloat($quote->getGrandTotal() - $shipping->getShippingAmount()),
                        ]
                    ],
                    'description' => 'Payment Request for ' . $store->getName(),
                    'payment_options' => [
                        'allowed_payment_method' => 'IMMEDIATE_PAY'
                    ]
                ]
            ],

        ];

        //TODO tratar preço dos diferentes tipos de produtos
        if ($items) {
            foreach ($items as $item) {
                $itemData = [
                    'name' => $item->getName(),
                    'description' => $item->getDescription(),
                    'quantity' => (int)$item->getQty(),
                    'price' => $this->convertFloat(
                        ($item->getRowTotal() - $item->getDiscountAmount()) / $item->getQty()
                    ),
                    'sku' => $item->getSku(),
                    'currency' => 'BRL'
                ];
                $payload['transactions'][0]['item_list']['items'][] = $itemData;
            }
        }

        if ($shipping) {
            $payload['transactions'][0]['item_list']['shipping_address'] = [
                'recipient_name' => $shipping->getName(),
                'line1' => sprintf(
                    "%s, %s - %s",
                    $this->customerFieldsMapper->getAddressStreet($quote, 'shipping_address'),
                    $this->customerFieldsMapper->getAddressStreetNumber($quote, 'shipping_address'),
                    $this->customerFieldsMapper->getAddressComplementary($quote, 'shipping_address')
                ),
                'line2' => $this->customerFieldsMapper->getAddressComplementary($quote, 'shipping_address'),
                'city' => $shipping->getCity(),
                'country_code' => 'BR',
                'postal_code' => $this->onlyNumbers($shipping->getPostcode()),
                'state' => $shipping->getRegion(),
                'phone' => $shipping->getTelephone()
            ];
        }

        try {
            $response = $this->client->createPaymentRequest($payload);
            if ($response->isSuccessful()) {
                return $response->getBody();
            }
            throw new \Exception(__("Cannot create payment request!" . $response->getBody()));
        } catch (\Exception $exception) {
            //TODO TRATAR EXCEPTION
            throw $exception;
        }
    }

    /**
     * @param Quote $quote
     */
    private function addData($quote)
    {
        $taxvat = $this->customerFieldsMapper->getCustomerCpf($quote);
        $taxvatType = strlen($taxvat) === 11 ? 'BR_CPF' : 'BR_CNPJ';
        if (strlen($taxvat) === 11) {
            $taxvatType = 'BR_CPF';
        } elseif (strlen($taxvat) === 14) {
            $taxvatType = 'BR_CNPJ';
        }
        $firstName = $quote->getCustomerFirstname();
        $lastName = $quote->getCustomerLastname();
        $shippingAddress = $quote->getShippingAddress();
        $email = $quote->getCustomerEmail();

        if (!$firstName && !$lastName) {
            $firstName = $shippingAddress->getFirstName();
            $lastName = $shippingAddress->getLastname();
        }

        if (!$email) {
            $email = $shippingAddress->getEmail() ?: $quote->getBillingAddress()->getEmail();
        }

        $rememberedCards = '';
        if ($quote->getCustomerId() && $quote->getCustomer()->getCustomAttribute('remembered_card_token')) {
            $rememberedCards = $quote->getCustomer()->getCustomAttribute('remembered_card_token')->getValue();
        }

        return [
            'payer_first_name' => $firstName,
            'payer_last_name' => $lastName,
            'payer_email' => $email,
            'payer_phone' => $quote->isVirtual()
                ? $this->customerFieldsMapper->getCustomerTelephone($quote, 'billing_address')
                : $this->customerFieldsMapper->getCustomerTelephone($quote, 'shipping_address'),
            'payer_tax_id' => $taxvat,
            'payer_tax_id_type' => $taxvatType,
            'mode' => ($this->generalConfig->getMode() === 'sandbox' ? 'sandbox' : 'live'),
            'allow_installments' => $this->creditCardConfig->getEnableInstallments(),
            'installments' => $this->creditCardConfig->getEnableInstallments() ? 0 : 1,
            'disallow_remembered_cards' =>
                ($quote->getCustomerId() && $this->creditCardConfig->getEnableSaveCreditCard()) ? false : true,
            'remembered_cards' => $rememberedCards,
            'iframe_height' => $this->creditCardConfig->getIframeHeight()
        ];
    }

}
