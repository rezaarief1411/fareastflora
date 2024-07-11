<?php

namespace Smartosc\Checkout\Model\ResourceModel\Quote\BundleAddon\Relation;

use Smartosc\Checkout\Model\Quote\BundleAddon\Relation;
use Smartosc\Checkout\Model\ResourceModel\Quote\BundleAddon\Relation as RelationResource;

/**
 * Class Collection
 * @package Smartosc\Checkout\Model\ResourceModel\Quote\BundleAddon\Relation
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Relation::class, RelationResource::class);
    }
}
