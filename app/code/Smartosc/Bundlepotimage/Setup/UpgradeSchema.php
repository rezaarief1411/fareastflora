<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Smartosc\Bundlepotimage\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    const TBL_BUNDLEPOT_IMAGE = 'bundle_pot_image';
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addColSkuName($installer);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->addColIdentifier($installer);
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->addColId($installer);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function addColSkuName(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $tableName = $connection->getTableName(self::TBL_BUNDLEPOT_IMAGE);
        if (!$connection->tableColumnExists($tableName, 'bundle_sku')) {
            $connection->addColumn($tableName, 'bundle_sku', [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Bundle Sku',
                'nullable' => false
            ]);
        }
        if (!$connection->tableColumnExists($tableName, 'pot_sku')) {
            $connection->addColumn($tableName, 'pot_sku', [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Pot Sku',
                'nullable' => false
            ]);
        }
        if (!$connection->tableColumnExists($tableName, 'bundle_name')) {
            $connection->addColumn($tableName, 'bundle_name', [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Bundle Name',
                'nullable' => true
            ]);
        }
        if (!$connection->tableColumnExists($tableName, 'pot_name')) {
            $connection->addColumn($tableName, 'pot_name', [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Pot Name',
                'nullable' => true
            ]);
        }
    }

    private function addColIdentifier(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $tableName = $connection->getTableName(self::TBL_BUNDLEPOT_IMAGE);
        if (!$connection->tableColumnExists($tableName, 'identifier')) {
            $connection->addColumn($tableName, 'identifier', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                'nullable' => true,
                'comment' => 'Identifier'
            ]);
        }
    }

    private function addColId(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $tableName = $connection->getTableName(self::TBL_BUNDLEPOT_IMAGE);
        if (!$connection->tableColumnExists($tableName, 'pot_id')) {
            $connection->addColumn($tableName, 'pot_id', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Pot ID'
            ]);
        }
        if (!$connection->tableColumnExists($tableName, 'bundle_id')) {
            $connection->addColumn($tableName, 'bundle_id', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Bundle ID'
            ]);
        }
    }
}
