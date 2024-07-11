<?php

namespace Smartosc\Showoutofstockprice\Model\ConfigurableProduct\ResourceModel\Product;

use Magento\Framework\DB\Select;

/**
 * Class StockStatusBaseSelectProcessor
 * @package Smartosc\Showoutofstockprice\Model\ConfigurableProduct\ResourceModel
 */
class StockStatusBaseSelectProcessor extends \Magento\ConfigurableProduct\Model\ResourceModel\Product\StockStatusBaseSelectProcessor
{
    /**
     * @params select
     */
    public function process(Select $select)
    {
        return $select;
    }
}
