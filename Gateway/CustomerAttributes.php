<?php

namespace Paypal\PaypalPlusBrasil\Gateway;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;

class CustomerAttributes extends GatewayConfig
{
    const KEY_CUSTOMER_COMPANY_LOGGEDIN = 'customer_company_loggedin';
    const KEY_CUSTOMER_CPF_LOGGEDIN = 'customer_cpf_loggedin';
    const KEY_CUSTOMER_CNPJ_LOGGEDIN = 'customer_cnpj_loggedin';
    const KEY_CUSTOMER_WEBSITE_LOGGEDIN = 'customer_website_loggedin';
    const KEY_CUSTOMER_TELEPHONE_LOGGEDIN = 'customer_telephone_loggedin';
    const KEY_CUSTOMER_FAX_LOGGEDIN = 'customer_fax_loggedin';
    const KEY_CUSTOMER_COMPANY_GUEST = 'customer_company_guest';
    const KEY_CUSTOMER_CPF_GUEST = 'customer_cpf_guest';
    const KEY_CUSTOMER_CNPJ_GUEST = 'customer_cnpj_guest';
    const KEY_CUSTOMER_WEBSITE_GUEST = 'customer_website_guest';
    const KEY_CUSTOMER_TELEPHONE_GUEST = 'customer_telephone_guest';
    const KEY_CUSTOMER_FAX_GUEST = 'customer_fax_guest';
    const KEY_ADDRESS_STREET = 'address_street';
    const KEY_ADDRESS_STREET_NUMBER = 'address_street_number';
    const KEY_ADDRESS_COMPLEMENTARY = 'address_complementary';
    const KEY_ADDRESS_NEIGHBORHOOD = 'address_neighborhood';

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = 'paypalplus_brasil/attributes_mapping',
        $pathPattern = '%s/%s'
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * @return string
     */
    public function getCustomerCompanyLoggedin()
    {
        return $this->getValue(self::KEY_CUSTOMER_COMPANY_LOGGEDIN);
    }

    /**
     * @return string
     */
    public function getCustomerCompanyGuest()
    {
        return $this->getValue(self::KEY_CUSTOMER_COMPANY_GUEST);
    }

    /**
     * @return string
     */
    public function getCustomerCpfLoggedin()
    {
        return $this->getValue(self::KEY_CUSTOMER_CPF_LOGGEDIN);
    }

    /**
     * @return string
     */
    public function getCustomerCpfGuest()
    {
        return $this->getValue(self::KEY_CUSTOMER_CPF_GUEST);
    }

    /**
     * @return string
     */
    public function getCustomerCnpjLoggedin()
    {
        return $this->getValue(self::KEY_CUSTOMER_CNPJ_LOGGEDIN);
    }

    /**
     * @return string
     */
    public function getCustomerCnpjGuest()
    {
        return $this->getValue(self::KEY_CUSTOMER_CNPJ_GUEST);
    }

    /**
     * @return string
     */
    public function getCustomerWebsiteLoggedin()
    {
        return $this->getValue(self::KEY_CUSTOMER_WEBSITE_LOGGEDIN);
    }

    /**
     * @return string
     */
    public function getCustomerWebsiteGuest()
    {
        return $this->getValue(self::KEY_CUSTOMER_WEBSITE_GUEST);
    }

    /**
     * @return string
     */
    public function getCustomerTelephoneLoggedin()
    {
        return $this->getValue(self::KEY_CUSTOMER_TELEPHONE_LOGGEDIN);
    }

    /**
     * @return string
     */
    public function getCustomerTelephoneGuest()
    {
        return $this->getValue(self::KEY_CUSTOMER_TELEPHONE_GUEST);
    }

    /**
     * @return string
     */
    public function getCustomerFaxLoggedin()
    {
        return $this->getValue(self::KEY_CUSTOMER_FAX_LOGGEDIN);
    }

    /**
     * @return string
     */
    public function getCustomerFaxGuest()
    {
        return $this->getValue(self::KEY_CUSTOMER_FAX_GUEST);
    }

    /**
     * @return string
     */
    public function getAddressStreet()
    {
        return $this->getValue(self::KEY_ADDRESS_STREET);
    }

    /**
     * @return string
     */
    public function getAddressStreetNumber()
    {
        return $this->getValue(self::KEY_ADDRESS_STREET_NUMBER);
    }

    /**
     * @return string
     */
    public function getAddressComplementary()
    {
        return $this->getValue(self::KEY_ADDRESS_COMPLEMENTARY);
    }

    /**
     * @return string
     */
    public function getAddressNeighbordhood()
    {
        return $this->getValue(self::KEY_ADDRESS_NEIGHBORHOOD);
    }
}
