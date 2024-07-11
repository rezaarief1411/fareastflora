<?php

namespace Smartosc\Checkout\Model\Quote;

/**
 * Class CustomPricing
 * @package Smartosc\Checkout\Model\Quote
 */
class CustomPricing
{
    /**
     * @var \Magento\Checkout\Model\Cart|\Magento\Quote\Model\Quote
     */
    private $_cart;

    /**
     * @var float|bool
     */
    private $_base_original_price;

    /**
     * @var float
     */
    private $_sum_row_total_incl_tax;

    /**
     * @var float|bool
     */
    private $_total_saving;

    /**
     * @return \Magento\Checkout\Model\Cart|\Magento\Quote\Model\Quote
     */
    public function getCart()
    {
        return $this->_cart;
    }

    /**
     * @param \Magento\Checkout\Model\Cart|\Magento\Quote\Model\Quote $cart
     *
     * @return $this
     */
    public function setCart($cart)
    {
        $this->_cart = $cart;
        return $this;
    }

    /**
     * @return bool|float
     */
    public function getTotalSaving() {
        $baseOriginalPrice = $this->getBaseOriginalPrice();
        $this->_total_saving = $baseOriginalPrice ? $baseOriginalPrice - $this->getSumRowTotalInclTax(): false;
        return  $this->_total_saving;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote() {

        $cart = $this->_cart;
        if ($cart instanceof \Magento\Quote\Model\Quote) {
            return $cart;
        } else {
            $quote = $cart->getQuote();
        }

        return $quote;
    }

    /**
     * Magento does not store base original price in db, so
     * this function is created to return it
     *
     * @return float|bool
     */
    public function getBaseOriginalPrice() {

        if (null !== $this->_base_original_price) {
            return $this->_base_original_price;
        }

        $baseOriginalPrice = 0;
        $sumRowTotalInclTax = 0;
        $quote = $this->getQuote();

        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $sumRowTotalInclTax += $item->getRowTotalInclTax();
            $product = $item->getProduct();
            $quantity = $item->getQty();
            if ($item->getProductType() === 'bundle') {

                foreach ($item->getChildren() as $selection) {
                    $baseOriginalPrice += $quantity * $selection->getProduct()->getPrice();
                }
            } else {
                $baseOriginalPrice += $product->getPrice() * $quantity;
            }
        }
        $this->_base_original_price = $baseOriginalPrice;
        $this->_sum_row_total_incl_tax = $sumRowTotalInclTax;
        
        return $baseOriginalPrice >  $sumRowTotalInclTax ? $baseOriginalPrice : false;
    }



    /**
     * Magento does not store sum of row total incl tax in db
     * (It only store grand total)
     * so this function is created to return a sum of row total incl tax
     *
     * @return float
     */
    public function getSumRowTotalInclTax() {
        return $this->_sum_row_total_incl_tax;
    }
}
