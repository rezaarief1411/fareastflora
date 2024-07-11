<?php

namespace Smartosc\Checkout\Model\ResourceModel\Quote\BundleAddon;

/**
 * Class Relation
 * @package Smartosc\Checkout\Model\ResourceModel\Quote\BundleAddon
 */
class Relation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    const TABLE_ADDON_BUNLDE = 'addon_bundle_relation';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_ADDON_BUNLDE, 'relation_id');
    }
}
