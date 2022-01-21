<?php

namespace Paypal\PaypalPlusBrasil\Observer\CreditCard;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Paypal\PaypalPlusBrasil\Logger\Logger;

class SaveRememberCardTokenObserver implements ObserverInterface
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Logger $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Logger $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customerId = $observer->getData('customer_id');
        $token = $observer->getData('token');
        try {
            $this->logger->info(__("Saving customer %1 remembered token", $customerId));
            $customer = $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute('remembered_card_token', $token);
            $this->customerRepository->save($customer);
        } catch (\Exception $exception) {
            $this->logger->error(__("Error on saving customer %1 token", $customerId));
            $this->logger->error(__($exception->getMessage()));
        }
    }
}
