<?php

namespace Smartosc\CustomLinked\Setup;

/**
 * Class InstallData
 *
 * Add new product link type
 */
class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $data = [
            ['link_type_id' => \Smartosc\CustomLinked\Model\Product\Link::LINK_TYPE_CUSTOMLINKED, 'code' => 'add_on'],
        ];
        
        foreach ($data as $bind) {
            $setup->getConnection()->insertForce($setup->getTable('catalog_product_link_type'), $bind);
        }
        
        $data = [
            [
                'link_type_id' => \Smartosc\CustomLinked\Model\Product\Link::LINK_TYPE_CUSTOMLINKED,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int',
            ]
        ];
        
        $setup->getConnection()->insertMultiple($setup->getTable('catalog_product_link_attribute'), $data);
    }
}
