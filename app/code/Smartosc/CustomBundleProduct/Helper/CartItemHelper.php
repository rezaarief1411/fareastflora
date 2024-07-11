<?php
namespace Smartosc\CustomBundleProduct\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data;
use Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory;
use  Magento\Sales\Model\Order\Item;

/**
 * Class CartItemHelper
 * @package Smartosc\CustomBundleProduct\Helper
 */
class CartItemHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ADDON_ITEM_ID = 'addon_item_id';
    const PRODUCT_ADDON_ID = 'product_addon_id';
    const MAIN_PRODUCT_ID = 'main_product_id';
    const BUNDLE_ITEM_ID = 'last_quote_item_id';

    /**
     * @var \Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory
     */
    protected $relationFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * CartItemHelper constructor.
     * @param Context $context
     * @param Data $jsonHelper
     * @param RelationFactory $relationFactory
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        RelationFactory $relationFactory
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->relationFactory = $relationFactory;
        parent::__construct($context);
    }

    /**
     * @param int $itemId
     * @return bool
     */
    public function isAddonItemId($itemId)
    {
        $addOnId = $this->relationFactory->create()
            ->getResourceCollection()
            ->addFieldToFilter(self::ADDON_ITEM_ID, $itemId)
            ->getFirstItem()
            ->getData(self::ADDON_ITEM_ID);

        return $addOnId ? true : false;
    }

    /**
     * @param Item $_item
     * @return array
     */
    public function getAddonList($_item)
    {
        $bundleID = $_item->getProductId();
        try {
            return $this->relationFactory->create()
                ->getResourceCollection()
                ->addFieldToFilter(self::MAIN_PRODUCT_ID, $bundleID)
                ->addFieldToFilter(self::BUNDLE_ITEM_ID, $_item->getQuoteItemId())
                ->getColumnValues(self::PRODUCT_ADDON_ID);
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param $item
     * @return array
     */
    public function getAddonItemIds(\Magento\Sales\Model\Order\Item $item) {
        $relation = $this->relationFactory->create();
        return $relation->getResourceCollection()
            ->addFieldToFilter(self::MAIN_PRODUCT_ID, $item->getProductId())
            ->addFieldToFilter(self::BUNDLE_ITEM_ID, $item->getQuoteItemId())
            ->getColumnValues(self::ADDON_ITEM_ID);
    }
}
