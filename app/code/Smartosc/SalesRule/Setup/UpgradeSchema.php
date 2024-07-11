<?php

namespace Smartosc\SalesRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.1') < 0) {
            $quoteTable = 'quote';
            //Quote table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteTable),
                    'label_discount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                        'default' => '',
                        'nullable' => true,
                        'comment' => 'Json Label Discount'
                    ]
                );
            $setup->endSetup();
        }
    }
}
