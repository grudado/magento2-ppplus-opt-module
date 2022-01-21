<?php

namespace Paypal\PaypalPlusBrasil\Controller\Checkout;

use Exception;
use Laminas\Json\Json;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory as QuoteIdMaskModelFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Paypal\PaypalPlusBrasil\Traits\FormatFields;

class CheckTotal implements ActionInterface
{
    use FormatFields;

    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var QuoteIdMaskModelFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskModelFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResource $quoteIdMaskResource
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskModelFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->cartRepository = $cartRepository;
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

        $json = $this->jsonFactory->create();
        $data = [];
        try {
            $quote = $this->getQuote($quote_id);
            $data = [
                'success' => true,
                'total' => $this->convertFloat($quote->getGrandTotal())
            ];
        } catch (Exception $exception) {
            $data['success'] = false;
            $data['responseText'] = Json::encode(['message' => $exception->getMessage()]);
        }

        return $json->setData($data);
    }

    /**
     * @param $quote_id
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function getQuote($quote_id): CartInterface
    {
        $id = (int)$quote_id;
        if (!$id) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $this->quoteIdMaskResource->load($quoteIdMask, $quote_id, 'masked_id');
            $quote_id = $quoteIdMask->getQuoteId();
        }
        return $this->cartRepository->get($quote_id);
    }

}
