<?php

namespace Smartosc\ResponsiveBanner\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * Add more fields
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    const TBL_BANNER = 'mageplaza_bannerslider_banner';
    const TBL_BANNER_SLIDER = 'mageplaza_bannerslider_slider';
    const URL_FOR_BANNER_TITLE = 'url_for_banner_title';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $tableName = $setup->getTable('mageplaza_bannerslider_banner');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();

                $connection->addColumn($tableName, 'position', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Position'
                ]);

                $connection->addColumn($tableName, 'color', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Color'
                ]);
            }

        }

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $tableName = $setup->getTable(self::TBL_BANNER);

            $columns = [
                'background_color' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Background Color'
                ],
                'vertical_position' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'vertical position for text'
                ]
            ];

            foreach ($columns as $name => $def) {
                $setup->getConnection()->addColumn($tableName, $name, $def);
            }
        }

        if (version_compare($context->getVersion(), '2.1.2', '<')) {
            $tableName = $setup->getTable(self::TBL_BANNER);

            $setup->getConnection()->addColumn($tableName, self::URL_FOR_BANNER_TITLE, [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'URL For Banner Title'
            ]);
        }

        if (version_compare($context->getVersion(), '2.1.3', '<')) {
            $tableName = $setup->getTable(self::TBL_BANNER_SLIDER);

            $setup->getConnection()->addColumn($tableName, 'enable_blog', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                ['nullable' => false, 'default' => '1'],
                'comment' => 'Enable For Bog'
            ]);

        }

	    if (version_compare($context->getVersion(), '2.1.3', '<')) {
		    $tableName = $setup->getTable(self::TBL_BANNER_SLIDER);

		    $setup->getConnection()->addColumn($tableName, 'enable_blog', [
			    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			    ['nullable' => false, 'default' => '1'],
			    'comment' => 'Enable For Bog'
		    ]);

	    }
        $setup->endSetup();
    }
}
