<?php

namespace Smartosc\CustomBundleProduct\Model\Checkout\CustomerData;

/**
 * Data structure for cart items sorting
 *
 * Class Cart
 * @package Smartosc\CustomBundleProduct\Model\Checkout\CustomerData
 */
class Cart
{
    /**
     * @var array
     */
    private $_items;

    /**
     * @var bool
     */
    private $_isSort = false;

    /**
     * @var \Smartosc\CustomBundleProduct\Helper\CartItemHelper
     */
    protected $addonHelper;

    /**
     * Cart constructor.
     *
     * @param \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper
     */
    public function __construct(
        \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper
    ) {
        $this->addonHelper = $addonHelper;
    }

    /**
     * @return $this
     */
    public function addSort()
    {
        $cartItems = $this->getItems();

        if (!$this->_isSort) {
            for ($i = count($cartItems) - 1, $j = $i - 1; $i > 0; $i--, $j--) {
                $tmp          = $cartItems[$i];
                $target       = $cartItems[$j];
                $targetItemId = $target['item_id'];
                if ($tmp['product_type'] !== 'bundle' || $target['product_type'] === 'bundle') {
                    continue;
                }
                if ($this->addonHelper->isAddonItemId($targetItemId)) {
                    $cartItems[$i] = $cartItems[$j];
                    $cartItems[$j] = $tmp;
                }
            }
            $this->_isSort = true;
        }

        $this->_items = $cartItems;

        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->_items = $items;

        return $this;
    }
}
