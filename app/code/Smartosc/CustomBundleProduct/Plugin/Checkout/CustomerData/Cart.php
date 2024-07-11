<?php

namespace Smartosc\CustomBundleProduct\Plugin\Checkout\CustomerData;

/**
 * Class Cart
 * @package Smartosc\CustomBundleProduct\Plugin\Checkout\CustomerData
 */
class Cart
{
    /**
     * @var \Smartosc\CustomBundleProduct\Model\Checkout\CustomerData\Cart
     */
    protected $cart;

    /**
     * Cart constructor.
     *
     * @param \Smartosc\CustomBundleProduct\Model\Checkout\CustomerData\Cart $cart
     */
    public function __construct(
        \Smartosc\CustomBundleProduct\Model\Checkout\CustomerData\Cart $cart
    ) {
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     *
     * @return array
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $output = $result;
        $cartItems = $this->cart
            ->setItems($output['items'])
            ->addSort()
            ->getItems();
        $output['items'] = $cartItems;

        return $output;
    }
}
