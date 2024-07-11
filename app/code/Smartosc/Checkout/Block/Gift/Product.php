<?php

namespace Smartosc\Checkout\Block\Gift;

/**
 * Class Product
 * @package Smartosc\Checkout\Block\Gift
 */
class Product extends \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
{
    /**
     * @return array
     */
    public function getProducts()
    {
        return parent::_getSelectedProducts();
    }
}
