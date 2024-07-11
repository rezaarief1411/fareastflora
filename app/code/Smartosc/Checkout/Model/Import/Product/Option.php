<?php
namespace Smartosc\Checkout\Model\Import\Product;

use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as ProductOptionValueCollectionFactory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Class Option
 * @package Smartosc\Checkout\Model\Import\Product
 */
class Option extends \Magento\CatalogImportExport\Model\Import\Product\Option
{
    /**
     * Option constructor.
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param array $data
     * @param ProductOptionValueCollectionFactory|null $productOptionValueCollectionFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
        ProcessingErrorAggregatorInterface $errorAggregator,
        array $data = [],
        ProductOptionValueCollectionFactory
        $productOptionValueCollectionFactory = null
    ) {
        parent::__construct($importData, $resource, $resourceHelper, $_storeManager, $productFactory, $optionColFactory, $colIteratorFactory, $catalogData, $scopeConfig, $dateTime, $errorAggregator, $data, $productOptionValueCollectionFactory);
    }

    /**
     * @return array
     */
    protected function _findNewOldOptionsTypeMismatch()
    {
        $errorRows = [];
        foreach ($this->_newOptionsOldData as $productId => $options) {
            foreach ($options as $outerData) {
                if (isset($this->_oldCustomOptions[$productId])) {
                    foreach ($this->_oldCustomOptions[$productId] as $innerData) {
                        if (count($outerData['titles']) == count($innerData['titles'])) {
                            $outerTitles = $outerData['titles'];
                            $innerTitles = $innerData['titles'];
                            ksort($outerTitles);
                            ksort($innerTitles);
                            if ($outerTitles === $innerTitles && $outerData['type'] === $innerData['type']) {
                                foreach ($outerData['rows'] as $dataRow) {
                                    $errorRows[] = $dataRow;
                                }
                            }
                        }
                    }
                }
            }
        }
        sort($errorRows);
        return $errorRows;
    }
}
