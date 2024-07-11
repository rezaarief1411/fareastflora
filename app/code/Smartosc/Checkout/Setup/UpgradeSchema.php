<?php
namespace Smartosc\Checkout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const QUOTE_TBL = 'quote';
    const SALES_ORDER_TBL = 'sales_order';
    const ADDON_BUNDLE_TBL = 'addon_bundle_relation';

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $attributes = [
            'pickup_comments' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                'default' => '',
                'nullable' => true,
                'comment' => 'Pick Up Comments'
            ],
            'pickup_date' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 100,
                'default' => '',
                'nullable' => true,
                'comment' => 'Pick Up date'
            ],
            'pickup_time' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 100,
                'default' => '',
                'nullable' => true,
                'comment' => 'Pick up Time'
            ]
        ];
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            foreach ($attributes as $attributeCode => $config) {
                //Quote table
                $setup->getConnection()
                    ->addColumn(
                        $setup->getTable(self::QUOTE_TBL),
                        $attributeCode,
                        $config
                    );
                //Order table
                $setup->getConnection()
                    ->addColumn(
                        $setup->getTable(self::SALES_ORDER_TBL),
                        $attributeCode,
                        $config
                    );
            }
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->addColumnDeliveryDate($setup);
        }
        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $this->addColumnDeliveryNote($setup);
        }
        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $this->addColumnShippingType($setup);
        }
        if (version_compare($context->getVersion(), '2.0.8', '<')) {
            $this->addColumnAddressBuildingFloor($setup);
        }
        if (version_compare($context->getVersion(), '2.0.9', '<')) {
            $this->changeDataTypeForColumnDeliveryNote($setup);
        }
        if (version_compare($context->getVersion(), '2.0.10', '<')) {
            $this->changeDataTypeForColumnOrderDeliveryNote($setup);
        }
        if (version_compare($context->getVersion(), '2.0.11', '<')) {
            $this->createTableToSaveAddonBundleRelation($setup);
        }
        if (version_compare($context->getVersion(), '2.0.13', '<')) {
            $this->AddColumnAddonBundleId($setup);
        }
        if (version_compare($context->getVersion(), '2.0.15', '<')) {
            $this->addFieldsGiftMessage($setup);
        }
        if (version_compare($context->getVersion(), '2.0.18', '<')) {
            $this->addStorePickupLocator($setup);
        }
        if (version_compare($context->getVersion(), '2.0.19', '<')) {
            $this->addAuthorize($setup);
        }

        $setup->endSetup();
    }

    /**
     * Change data type from varchar to text
     * to fix issue text limit by 255 characters
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     */
    protected function changeDataTypeForColumnDeliveryNote($setup)
    {
        $installer = $setup;
        $installer->getConnection()->modifyColumn(
            $installer->getTable('quote'),
            'delivery_note',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                'default' => '',
                'nullable' => true,
                'comment' => 'Delivery Note'
            ]
        );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     */
    protected function changeDataTypeForColumnOrderDeliveryNote($setup)
    {
        $installer = $setup;
        $installer->getConnection()->modifyColumn(
            $installer->getTable('sales_order'),
            'delivery_note',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                'default' => '',
                'nullable' => true,
                'comment' => 'Delivery Note'
            ]
        );
    }

    protected function addColumnShippingType(SchemaSetupInterface $setup)
    {
        $attributes = [
            'shipping_type' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 100,
                'default' => '',
                'nullable' => true,
                'comment' => 'Shipping type'
            ]
        ];

        foreach ($attributes as $attributeCode => $config) {
            //Quote table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::QUOTE_TBL),
                    $attributeCode,
                    $config
                );
            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::SALES_ORDER_TBL),
                    $attributeCode,
                    $config
                );
        }
    }

    protected function addColumnDeliveryDate(SchemaSetupInterface $setup)
    {
        $attributes = [
            'delivery_date' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 100,
                'default' => '',
                'nullable' => true,
                'comment' => 'Delivery date'
            ]
        ];

        foreach ($attributes as $attributeCode => $config) {
            //Quote table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::QUOTE_TBL),
                    $attributeCode,
                    $config
                );
            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::SALES_ORDER_TBL),
                    $attributeCode,
                    $config
                );
        }
    }

    protected function addColumnDeliveryNote(SchemaSetupInterface $setup)
    {
        $attributes = [
            'delivery_note' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'nullable' => true,
                'comment' => 'Delivery Note'
            ]
        ];

        foreach ($attributes as $attributeCode => $config) {
            //Quote table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::QUOTE_TBL),
                    $attributeCode,
                    $config
                );
            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::SALES_ORDER_TBL),
                    $attributeCode,
                    $config
                );
        }
    }

    protected function addColumnAddressBuildingFloor(SchemaSetupInterface $setup)
    {
        $attributes = [
            'shipping_building' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'nullable' => true,
                'comment' => 'shipping_building'
            ],
            'shipping_floor' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'nullable' => true,
                'comment' => 'shipping_floor'
            ],
            'billing_building' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'nullable' => true,
                'comment' => 'billing_building'
            ],
            'billing_floor' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'nullable' => true,
                'comment' => 'billing_floor'
            ]
        ];

        foreach ($attributes as $attributeCode => $config) {
            //Quote table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::QUOTE_TBL),
                    $attributeCode,
                    $config
                );
            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::SALES_ORDER_TBL),
                    $attributeCode,
                    $config
                );
        }
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     *
     * @return void
     */
    protected function createTableToSaveAddonBundleRelation($setup)
    {
        $installer = $setup;
        $tableName = $installer->getTable(self::ADDON_BUNDLE_TBL);
        $this->dropTableIfExists($installer, $tableName);
        /** @var  \Magento\Framework\DB\Ddl\Table $relationTable */
        $relationTable = $installer->getConnection()->newTable($tableName);

        $relationTable
            ->addColumn(
                'relation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'primary' => true,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Primary Key'
            )->addColumn(
                'main_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Main Product ID'
            )
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Quote ID'
            )->addColumn(
                'product_addon_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Product Addon ID'
            )->addColumn(
                'last_quote_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Last Quote Item ID'
            )->addIndex(
                $installer->getIdxName($tableName, ['relation_id']),
                ['relation_id']
            )->addForeignKey(
                $installer->getFkName($tableName, 'last_quote_item_id', $installer->getTable('quote_item'), 'item_id'),
                'last_quote_item_id',
                $installer->getTable('quote_item'),
                'item_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
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
            ->setComment('This table is used to store the relationship between a bundle product and addon product');

        $installer->getConnection()->createTable($relationTable);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param string $table
     */
    private function dropTableIfExists($installer, $table)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable($table))) {
            $installer->getConnection()->dropTable(
                $installer->getTable($table)
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function AddColumnAddonBundleId($setup)
    {
        $attributes = [
            'addon_item_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Addon Item ID'
            ]
        ];

        foreach ($attributes as $attributeCode => $config) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::ADDON_BUNDLE_TBL),
                    $attributeCode,
                    $config
                );
        }
    }

    private function addFieldsGiftMessage(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $attributes = [
            'gift_message_from' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'comment' => 'Gift Message From',
                'nullable' => true,
            ],
            'gift_message_to' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'comment' => 'Gift Message To',
                'nullable' => true,
            ],
            'gift_message' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                'default' => '',
                'comment' => 'Gift Message',
                'nullable' => true,
            ]
        ];

        foreach ($attributes as $columnName => $definition) {
            $setup->getConnection()
                  ->addColumn(
                      $setup->getTable(self::QUOTE_TBL),
                      $columnName,
                      $definition
                  );
            $setup->getConnection()
                  ->addColumn(
                      $setup->getTable(self::SALES_ORDER_TBL),
                      $columnName,
                      $definition
                  );
        }
    }

    private function addStorePickupLocator(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $attributes = [
            'pickup_store_name' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'comment' => 'Store Name',
                'nullable' => true,
            ],
            'pickup_store_address' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'comment' => 'Store Address',
                'nullable' => true,
            ],
            'pickup_store_state' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default' => '',
                'comment' => 'Store State',
                'nullable' => true,
            ],
            'pickup_store_zip' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 25,
                'default' => '',
                'comment' => 'Store Zip',
                'nullable' => true,
            ]
        ];

        foreach ($attributes as $columnName => $definition) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::QUOTE_TBL),
                    $columnName,
                    $definition
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::SALES_ORDER_TBL),
                    $columnName,
                    $definition
                );
        }
    }

    private function addAuthorize(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $attributes = [
            'accept_authorize' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'default' => null,
                'comment' => 'Authorize',
                'nullable' => true
            ]
        ];

        foreach ($attributes as $columnName => $definition) {
            $setup->getConnection()
                  ->addColumn(
                      $setup->getTable(self::QUOTE_TBL),
                      $columnName,
                      $definition
                  );
            $setup->getConnection()
                  ->addColumn(
                      $setup->getTable(self::SALES_ORDER_TBL),
                      $columnName,
                      $definition
                  );
        }
    }
}
