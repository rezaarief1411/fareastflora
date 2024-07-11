<?php

namespace Smartosc\Checkout\Plugin\Sidebar;

/**
 * Class RemoveAddon
 * @package Smartosc\Checkout\Plugin\Sidebar
 */
class RemoveAddon extends \Smartosc\Checkout\Plugin\AbstractRemoveItem
{
    /**
     * {@inheritdoc}
     */
    function getRequestItemId($request)
    {
        return (int)$request->getParam('item_id');
    }

    /**
     * {@inheritdoc}
     */
    function removeItem($itemId)
    {
        $quote = $this->_cart->getQuote();
        $quote->removeItem($itemId);
    }
}
