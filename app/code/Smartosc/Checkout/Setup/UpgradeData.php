<?php

namespace Smartosc\Checkout\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Smartosc\Checkout\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * UpgradeData constructor.
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '2.0.17') < 0) {
            $this->addFloorAttribute($customerSetup);
            $this->addBillingNameAttribute($customerSetup);
        }
    }

    /**
     * @param $customerSetup
     */
    public function addFloorAttribute($customerSetup)
    {
        $customerSetup->addAttribute('customer_address', 'floor', [
            'label' => 'Floor/Unit',
            'input' => 'text',
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'source' => '',
            'required' => false,
            'position' => 90,
            'visible' => true,
            'system' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'frontend_input' => 'hidden',
            'backend' => ''
        ]);

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute('customer_address', 'floor')
            ->addData([
                'used_in_forms' => [
                    'adminhtml_customer_address',
                    'adminhtml_customer',
                    'customer_address_edit',
                    'customer_register_address',
                    'customer_address',
                ]
            ]);
        $attribute->save();
    }

    /**
     * @param $customerSetup
     */
    public function addBillingNameAttribute($customerSetup)
    {
        $customerSetup->addAttribute('customer_address', 'building', [
            'label' => 'Building Name',
            'input' => 'text',
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'source' => '',
            'required' => false,
            'position' => 100,
            'visible' => true,
            'system' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'frontend_input' => 'hidden',
            'backend' => ''
        ]);

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute('customer_address', 'building')
            ->addData([
                'used_in_forms' => [
                    'adminhtml_customer_address',
                    'adminhtml_customer',
                    'customer_address_edit',
                    'customer_register_address',
                    'customer_address',
                ]
            ]);
        $attribute->save();
    }
}
