<?php

namespace Paypal\PaypalPlusBrasil\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class Status implements OptionSourceInterface
{
    protected $state = '';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collection
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create()->addStateFilter($this->state);
        return $collection->toOptionArray();
    }
}
