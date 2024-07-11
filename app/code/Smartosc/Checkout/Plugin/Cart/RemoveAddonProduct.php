<?php

namespace Smartosc\Checkout\Plugin\Cart;

/**
 * Class RemoveAddonProduct
 * @package Smartosc\Checkout\Plugin\Cart
 */
class RemoveAddonProduct extends \Smartosc\Checkout\Plugin\AbstractRemoveItem
{
    /**
     * {@inheritdoc}
     */
    function getRequestItemId($request)
    {
        return (int)$request->getParam('id');
    }

    /**
     * {@inheritdoc}
     */
    function removeItem($itemId)
    {
        $this->_cart->getQuote()->removeItem($itemId);
        $this->_cart->save();
    }
}
