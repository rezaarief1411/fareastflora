<?php

namespace Smartosc\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const REPOTTED_OPTION_NAMES = 'repotted_option/general/repotted_option_names';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    protected $stockRegistry;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
    }

    public function getStoreConfig($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    public function getRepottedOptionNames()
    {
        $repottedOptionNames = $this->getStoreConfig(self::REPOTTED_OPTION_NAMES);

        return explode(",", $repottedOptionNames);
    }

    public function getStockItem($productId)
    {
        return $this->stockRegistry->getStockItem($productId)->getIsInStock();
    }
}
