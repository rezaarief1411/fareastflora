<?php

namespace Smartosc\Checkout\Model\Quote\BundleAddon;

/**
 * Class Relation
 * @package Smartosc\Checkout\Model\Quote\BundleAddon
 */
class Relation extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Smartosc\Checkout\Model\ResourceModel\Quote\BundleAddon\Relation::class);
    }
}
