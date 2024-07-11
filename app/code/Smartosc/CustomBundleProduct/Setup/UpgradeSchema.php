<?php

namespace Smartosc\CustomBundleProduct\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    const TBL_BUNDLEPOT_IMAGE = 'bundle_pot_image';

    private $quoteSetupFactory;


    private $salesSetupFactory;

    /**
     * UpgradeSchema constructor.
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    )
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $table = $setup->getConnection()->newTable($setup->getTable(self::TBL_BUNDLEPOT_IMAGE))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Image Id'
                )
                ->addColumn(
                    'bundle_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Bundle Id'
                )
                ->addColumn('option_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'option id'
                )
                ->addColumn('selection_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'selection_id'
                )->addColumn(
                    'bundle_pot_image',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                    [],
                    'Bundle Pot Image'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Creation Time'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                )
                ->addIndex(
                    $setup->getIdxName(self::TBL_BUNDLEPOT_IMAGE, ['id']),
                    ['id']
                )
                ->addIndex(
                    $setup->getIdxName(self::TBL_BUNDLEPOT_IMAGE, ['bundle_id']),
                    ['bundle_id']

                )->addForeignKey(
                    $setup->getFkName(self::TBL_BUNDLEPOT_IMAGE, 'selection_id', 'catalog_product_bundle_selection',
                        'selection_id'),
                    'selection_id',
                    $setup->getTable('catalog_product_bundle_selection'),
                    'selection_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment('Bundle Pot Image');

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('sales_order_item'),
                'is_repot_not_require',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'comment' => 'check has product repot not require',
                    'default' => false
                ]
            );

            $connection->addColumn(
                $setup->getTable('quote_item'),
                'is_repot_not_require',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'comment' => 'check has product repot not require',
                    'default' => false
                ]
            );
        }
        $setup->endSetup();
    }
}
