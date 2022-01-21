<?php

namespace Paypal\PaypalPlusBrasil\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Zend_Validate_Exception;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateCustomerRememberCardTokenAttribute implements DataPatchInterface
{
    const REMEMBERED_CARD_TOKEN = 'remembered_card_token';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;

    /**
     * @var SetFactory
     */
    private SetFactory $attributeSetFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        SetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException|Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        try {
            $customerSetup->addAttribute(Customer::ENTITY, self::REMEMBERED_CARD_TOKEN, [
                'type' => 'varchar',
                'label' => 'Remembered',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => false,
                'position' => 210,
                'system' => false,
            ]);

            $attribute = $customerSetup->getEavConfig()
                ->getAttribute(Customer::ENTITY, self::REMEMBERED_CARD_TOKEN);
            $attribute->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                ]
            );
            $attribute->save();

            $this->moduleDataSetup->getConnection()->endSetup();
        } catch (\Exception $exception) {
            $this->moduleDataSetup->getConnection()->rollBack();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
