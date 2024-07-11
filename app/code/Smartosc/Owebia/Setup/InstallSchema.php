<?php

namespace Smartosc\Owebia\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $eavSetup
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavSetup = $eavSetup;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * @var \Magento\Eav\Setup\EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->eavSetup]);
        $installer = $setup;
        $options['values'] = [
            '8',
            '9',
            '10',
            '12',
            '13',
            '14',
            '50cm',
            '60cm',
            '70cm',
            '80cm',
            '90cm',
        ];
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'size_pot',
            [
                'group' => '',
                'type' => 'varchar',
                'input' => 'select',
                'label' => 'Size of pot',
                'backend' => null,
                'source' => null,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'unique' => false,
                'apply_to' => '',
                'option' => $options,
            ]
        );
        $attributeGroup = 'General'; // Attribute Group Name
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $allAttributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($allAttributeSetIds as $attributeSetId) {
            $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $attributeGroup);
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupId,
                'size_pot',
                999
            );
        }
        $installer->startSetup();
        $installer->endSetup();
    }
}