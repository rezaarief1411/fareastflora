<?php

namespace Smartosc\Checkout\Plugin;

/**
 * Class AbstractRemoveItem
 * @package Smartosc\Checkout\Plugin
 */
abstract class AbstractRemoveItem
{
    const BUNDLE_ITEM_ID = 'last_quote_item_id';
    const ADDON_ITEM_ID = 'addon_item_id';

    /**
     * @var \Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory
     */
    protected $_relationFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * AbstractRemoveItem constructor.
     * @param \Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory $_relationFactory
     */
    public function __construct(
        \Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory $relationFactory,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->_relationFactory = $relationFactory;
        $this->_cart = $cart;
    }

    /**
     * @param int $itemId
     * @return array
     */
    private function _getAddonItemIds($itemId)
    {
        try {
            return $this->_relationFactory->create()
                ->getResourceCollection()
                ->addFieldToFilter(self::BUNDLE_ITEM_ID, $itemId)
                ->getColumnValues(self::ADDON_ITEM_ID);
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param \Magento\Framework\App\Action\HttpPostActionInterface $subject
     * @return void
     */
    public function beforeExecute($subject)
    {
        $request = $subject->getRequest();
        $requestItemId = $this->getRequestItemId($request);
        $itemIds = $this->_getAddonItemIds($requestItemId);

        foreach ($itemIds as $itemId) {
            $this->removeItem($itemId);
        }
    }

    /**
     * @param $request
     * @return int
     */
    abstract protected function getRequestItemId($request);

    /**
     * @param int $itemId
     * @return void
     */
    abstract protected function removeItem($itemId);
}
