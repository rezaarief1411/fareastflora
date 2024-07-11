<?php

namespace Smartosc\CustomBundleProduct\Plugin;

/**
 * Class BundleTypePlugin
 * @package Smartosc\CustomBundleProduct\Plugin
 */
class BundleTypePlugin
{
    /**
     * @param \Magento\Catalog\Model\Product\Type\AbstractType $subject
     * @param $result
     * @param $product
     *
     * @return bool
     */
    public function afterIsPossibleBuyFromList(\Magento\Catalog\Model\Product\Type\AbstractType $subject, $result, $product)
    {
        if ($subject instanceof \Magento\Bundle\Model\Product\Type) {
            return true;
        }

        return $result;
    }
}