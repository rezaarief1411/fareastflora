<?php

namespace Smartosc\Checkout\Plugin\Cart\Totals;

use Magento\Catalog\Model\Product\Type;

/**
 * Class ItemConverterPlugin
 * @package Smartosc\Checkout\Plugin\Cart\Totals
 */
class ItemConverterPlugin
{
    /**
     * @var int
     */
    private $_itemId;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $_item;

    /**
     * @var \Magento\Quote\Api\Data\TotalsItemExtensionFactory
     */
    protected $totalsItemExtensionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $reportService;

    /**
     * @var \Magento\Quote\Api\Data\TotalsItemInterfaceFactory
     */
    protected $totalsItemFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Cart\Totals\ItemConverter
     */
    protected $itemConverter;

    /**
     * @var \Smartosc\CustomBundleProduct\Helper\CartItemHelper
     */
    protected $addonHelper;

    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_PRODUCT_ID = 'product_id';

    const KEY_PARENT_ITEM_ID = 'parent_item_id';

    const KEY_SKU = 'sku';

    const KEY_QUOTE_ID = 'quote_id';

    /**
     * ItemConverterPlugin constructor.
     *
     * @param \Magento\Quote\Model\Quote\ItemFactory $itemFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalsItemExtensionFactory
     * @param \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService
     * @param \Magento\Quote\Api\Data\TotalsItemInterfaceFactory $totalsItemFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Quote\Model\Cart\Totals\ItemConverter $itemConverter
     * @param \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper
     */
    public function __construct(
        \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalsItemExtensionFactory,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService,
        \Magento\Quote\Api\Data\TotalsItemInterfaceFactory $totalsItemFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Quote\Model\Cart\Totals\ItemConverter $itemConverter,
        \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper
    ) {
        $this->itemCollectionFactory      = $itemCollectionFactory;
        $this->itemFactory                = $itemFactory;
        $this->quoteFactory               = $quoteFactory;
        $this->totalsItemExtensionFactory = $totalsItemExtensionFactory;
        $this->totalsItemFactory          = $totalsItemFactory;
        $this->reportService              = $reportService;
        $this->itemConverter = $itemConverter;
        $this->addonHelper = $addonHelper;
        $this->_item = $this->itemFactory->create();
    }

    /**
     * @param \Magento\Quote\Model\Cart\Totals\ItemConverter $subject
     * @param $result
     * @param $item
     * @return mixed
     */
    public function afterModelToDataObject(
        \Magento\Quote\Model\Cart\Totals\ItemConverter $subject,
        $result,
        $item
    ) {
        $this->_itemId       = $result->getItemId();
        $extensionAttributes = $result->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->totalsItemExtensionFactory->create();
        }
        $extensionAttributes->setSku($this->_trimSku($item))
                            ->setProductId($item->getData(self::KEY_PRODUCT_ID))
                            ->setProductType($item->getRealProductType());
        if ($item->getProductType() == Type::TYPE_BUNDLE) {

            if ($item->getIsRepotNotRequire()) {
                $extensionAttributes->setReportService($this->reportService);
            }
            $childItemCollection = $this->itemCollectionFactory->create()
                ->addFieldToFilter(self::KEY_QUOTE_ID, $item->getQuoteId())
                ->addFieldToFilter(self::KEY_PARENT_ITEM_ID, $item->getItemId());
            $smartOptions = $this->getSmartOptions($childItemCollection);
            $extensionAttributes->setSmartOptions($smartOptions);
            $extensionAttributes->setUsePseudo($item->getIsRepotNotRequire());
        } else {
            $isAddOn = $this->_isAddon($item);
            $extensionAttributes->setIsAddon($isAddOn);
        }
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }

    /**
     * @param int $itemId
     *
     * @return \Magento\Quote\Model\Quote\Item
     */
    protected function getItem()
    {
        if (null !== $this->_itemId) {
            $item  = $this->_item->load($this->_itemId);
            $quote = $this->quoteFactory->create()
                                        ->load($item->getQuoteId());
            $this->_item->setQuote($quote);
        }

        return $this->_item;
    }


    /**
     * @return \Magento\Quote\Api\Data\TotalsItemInterface[]
     */
    protected function getSmartOptions($childItemCollection)
    {
        $items      = [];
        foreach ($childItemCollection as $item) {
            $totalsItem          = $this->itemConverter->modelToDataObject($item);
            $extensionAttributes = $totalsItem->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->totalsItemExtensionFactory->create();
            }
            $extensionAttributes->setSku($this->_trimSku($item));

            $product = $item->getProduct();
            if ($product->getPrice() > $product->getFinalPrice()) {
                $extensionAttributes->setOldPrice($product->getPrice());
            }

            $totalsItem->setExtensionAttributes($extensionAttributes);
            $items[] = $totalsItem;
        }

        return $items;
    }

    /**
     * Return true if user pick a repotting service
     * Return false if user doesnot pick a repotting service
     *
     * @return bool
     */
    private function _hasRepot($childItemCollection)
    {
        foreach ($childItemCollection as $item) {

            $productId = $item->getData(self::KEY_PRODUCT_ID);
            if ($this->reportService->isReportServiceProduct($productId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return string
     */
    private function _trimSku($item)
    {
        return trim($item->getSku(), " \"\t.");
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    private function _isAddon($item)
    {
        return $this->addonHelper->isAddonItemId($item->getItemId());
    }
}
