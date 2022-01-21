<?php

namespace Paypal\PaypalPlusBrasil\Model\Mapper;

use Paypal\PaypalPlusBrasil\Gateway\CustomerAttributes;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class CustomerFieldsMapper
{
    protected $customerAttributesConfig;
    protected $customerRepository;

    public function __construct(
        CustomerAttributes $customerAttributes,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerAttributesConfig = $customerAttributes;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Quote|Order $object
     * @return string
     */
    public function getCustomerCpf($object)
    {
        $value = null;

        if (!$object->getCustomerId()) {
            $field = $this->customerAttributesConfig->getCustomerCpfGuest();
            $field = $this->cleanField($field);
            $value = $object->getBillingAddress()->getData($field);
        } else {
            $field = $this->customerAttributesConfig->getCustomerCpfLoggedin();
            if ($this->_getFieldType($field) === 'customer') {
                $field = $this->cleanField($field);
                $value = $this->extractCustomerFieldValue($object->getCustomerId(), $field);
            } else {
                if ($this->_getFieldType($field) === 'address') {
                    $field = $this->cleanField($field);
                    $value = $object->getBillingAddress()->getData($field);
                }
            }
        }

        return preg_replace('/\D+/', '', $value);
    }

    /**
     * @param Quote|Order $object
     * @return string
     */
    public function getCustomerCnpj($object)
    {
        $value = null;

        if (!$object->getCustomerId()) {
            $field = $this->customerAttributesConfig->getCustomerCnpjGuest();
            $field = $this->cleanField($field);
            $value = $object->getBillingAddress()->getData($field);
        } else {
            $field = $this->customerAttributesConfig->getCustomerCnpjLoggedin();
            if ($this->_getFieldType($field) === 'customer') {
                $field = $this->cleanField($field);
                $value = $this->extractCustomerFieldValue($object->getCustomerId(), $field);
            } else {
                if ($this->_getFieldType($field) === 'address') {
                    $field = $this->cleanField($field);
                    $value = $object->getBillingAddress()->getData($field);
                }
            }
        }

        return preg_replace('/\D+/', '', $value);
    }

    /**
     * @param Quote|Order $object
     * @return string
     */
    public function getCustomerCompany($object)
    {
        $value = null;

        if (!$object->getCustomerId()) {
            $field = $this->customerAttributesConfig->getCustomerCompanyGuest();
            $field = $this->cleanField($field);
            $value = $object->getBillingAddress()->getData($field);
        } else {
            $field = $this->customerAttributesConfig->getCustomerCompanyLoggedin();
            if ($this->_getFieldType($field) === 'customer') {
                $field = $this->cleanField($field);
                $value = $this->extractCustomerFieldValue($object->getCustomerId(), $field);
            } else {
                if ($this->_getFieldType($field) === 'address') {
                    $field = $this->cleanField($field);
                    $value = $object->getBillingAddress()->getData($field);
                }
            }
        }

        return $value;
    }

    /**
     * @param Quote|Order $object
     * @return string
     */
    public function getCustomerWebsite($object)
    {
        $value = null;

        if (!$object->getCustomerId()) {
            $field = $this->customerAttributesConfig->getCustomerWebsiteGuest();
            $field = $this->cleanField($field);
            $value = $object->getBillingAddress()->getData($field);
        } else {
            $field = $this->customerAttributesConfig->getCustomerWebsiteLoggedin();
            if ($this->_getFieldType($field) === 'customer') {
                $field = $this->cleanField($field);
                $value = $this->extractCustomerFieldValue($object->getCustomerId(), $field);
            } else {
                if ($this->_getFieldType($field) === 'address') {
                    $field = $this->cleanField($field);
                    $value = $object->getBillingAddress()->getData($field);
                }
            }
        }

        return $value;
    }

    /**
     * @param Quote|Order $object
     * @param string $addressType
     * @return string
     */
    public function getCustomerTelephone($object, $addressType)
    {
        $value = null;
        $addressType = 'get' . str_replace('_', '', ucwords($addressType, '_'));

        if (!$object->getCustomerId()) {
            $field = $this->customerAttributesConfig->getCustomerTelephoneGuest();
            $field = $this->cleanField($field);
            $value = $object->$addressType()->getData($field);
        } else {
            $field = $this->customerAttributesConfig->getCustomerTelephoneLoggedin();
            if ($this->_getFieldType($field) === 'customer') {
                $field = $this->cleanField($field);
                $value = $this->extractCustomerFieldValue($object->getCustomerId(), $field);
            } else {
                if ($this->_getFieldType($field) === 'address') {
                    $field = $this->cleanField($field);
                    $value = $object->$addressType()->getData($field);
                }
            }
        }

        return $value;
    }

    /**
     * @param Quote|Order $object
     * @return string
     */
    public function getCustomerFax($object)
    {
        $value = null;

        if (!$object->getCustomerId()) {
            $field = $this->customerAttributesConfig->getCustomerFaxGuest();
            $field = $this->cleanField($field);
            $value = $object->getBillingAddress()->getData($field);
        } else {
            $field = $this->customerAttributesConfig->getCustomerFaxLoggedin();
            if ($this->_getFieldType($field) === 'customer') {
                $field = $this->cleanField($field);
                $value = $this->extractCustomerFieldValue($object->getCustomerId(), $field);
            } else {
                if ($this->_getFieldType($field) === 'address') {
                    $field = $this->cleanField($field);
                    $value = $object->getBillingAddress()->getData($field);
                }
            }
        }

        return $value;
    }

    /**
     * @param Order|Quote $object
     * @param string $addressType
     * @return string
     */
    public function getAddressStreet($object, $addressType)
    {
        $field = $this->customerAttributesConfig->getAddressStreet();
        $field = $this->cleanField($field);
        $addressType = 'get' . str_replace('_', '', ucwords($addressType, '_'));

        if (strpos($field, 'street_') !== false) {
            $street = $object->$addressType()->getStreet();
            $line = (int)str_replace('street_', '', $field) - 1;
            $value = $street[$line] ?? null;
        } else {
            $value = $object->$addressType()->getData($field);
        }

        return $value;
    }

    /**
     * @param Order|Quote $object
     * @param string $addressType
     * @return string
     */
    public function getAddressStreetNumber($object, $addressType)
    {
        $field = $this->customerAttributesConfig->getAddressStreetNumber();
        $field = $this->cleanField($field);
        $addressType = 'get' . str_replace('_', '', ucwords($addressType, '_'));

        if (strpos($field, 'street_') !== false) {
            $street = $object->$addressType()->getStreet();
            $line = (int)str_replace('street_', '', $field) - 1;
            $value = $street[$line] ?? null;
        } else {
            $value = $object->$addressType()->getData($field);
        }

        return $value;
    }

    /**
     * @param Order|Quote $object
     * @param string $addressType
     * @return string
     */
    public function getAddressComplementary($object, $addressType)
    {
        $field = $this->customerAttributesConfig->getAddressComplementary();
        $field = $this->cleanField($field);
        $addressType = 'get' . str_replace('_', '', ucwords($addressType, '_'));

        if (strpos($field, 'street_') !== false) {
            $street = $object->$addressType()->getStreet();
            $line = (int)str_replace('street_', '', $field) - 1;
            $value = $street[$line] ?? null;
        } else {
            $value = $object->$addressType()->getData($field);
        }

        return $value;
    }

    /**
     * @param Order|Quote $order
     * @return string
     */
    public function getAddressNeighbordhood($object, $addressType)
    {
        $field = $this->customerAttributesConfig->getAddressNeighbordhood();
        $field = $this->cleanField($field);
        $addressType = 'get' . str_replace('_', '', ucwords($addressType, '_'));

        if (strpos($field, 'street_') !== false) {
            $street = $object->$addressType()->getStreet();
            $line = (int)str_replace('street_', '', $field) - 1;
            $value = $street[$line] ?? null;
        } else {
            $value = $object->$addressType()->getData($field);
        }

        return $value;
    }

    /**
     * @param $field
     * @return string
     */
    private function _getFieldType($field)
    {
        $type = 'customer';
        if (strpos($field, 'address_') === 0) {
            $type = 'address';
        }
        return $type;
    }

    /**
     * @param $field
     * @return array|string|string[]|null
     */
    private function cleanField($field)
    {
        $field = preg_replace('/^customer_/', '', $field);
        $field = preg_replace('/^address_/', '', $field);
        return $field;
    }

    /**
     * @param $customer
     * @param $billingAddress
     * @param $field
     */
    private function extractCustomerFieldValue($customerId, $field)
    {
        $value = null;

        $customer = $this->customerRepository->getById($customerId);

        $method = 'get' . str_replace('_', '', ucwords($field, '_'));

        if (method_exists($customer, $method)) {
            $value = $customer->$method();
        } elseif ($customer->getCustomAttribute($field) && $customer->getCustomAttribute($field)->getValue()) {
            $value = $customer->getCustomAttribute($field)->getValue();
        }

        return $value;
    }
}
