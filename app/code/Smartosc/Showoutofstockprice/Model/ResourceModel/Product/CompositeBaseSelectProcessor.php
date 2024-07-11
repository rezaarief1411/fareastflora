<?php

namespace Smartosc\Showoutofstockprice\Model\ResourceModel\Product;

use Magento\CatalogInventory\Model\ResourceModel\Product\StockStatusBaseSelectProcessor;

/**
 * Class CompositeBaseSelectProcessor
 * @package Smartosc\Showoutofstockprice\Model\ResourceModel\Product
 */
class CompositeBaseSelectProcessor extends \Magento\Catalog\Model\ResourceModel\Product\CompositeBaseSelectProcessor
{
    /**
     * CompositeBaseSelectProcessor constructor.
     * @param array $baseSelectProcessors
     * @throws \Magento\Framework\Exception\InputException
     */
    public function __construct(
        array $baseSelectProcessors
    ) {
        // REMOVE THE STOCK STATUS PROCESSOR
        //...................................
        $finalProcessors = [];
        foreach ($baseSelectProcessors as $baseSelectProcessor) {
            if (!is_a($baseSelectProcessor, StockStatusBaseSelectProcessor::class)) {
                $finalProcessors[] = $baseSelectProcessor;
            }
        }

        parent::__construct($finalProcessors);
    }
}
