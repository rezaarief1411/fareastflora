<?php

namespace Smartosc\Checkout\Model\Quote\Address\Total;

/**
 * Class BaseOriginalPrice
 * @package Smartosc\Checkout\Model\Quote\Address\Total
 */
class BaseOriginalPrice extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */

    /**
     * @var \Smartosc\Checkout\Model\Quote\CustomPricingFactory
     */
    protected $customPricingFactory;

    /**
     * BaseOriginalPrice constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Helper\Data $taxHelper
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Smartosc\Checkout\Model\Quote\CustomPricingFactory $customPricingFactory,
        \Magento\Catalog\Helper\Data $taxHelper,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->customPricingFactory = $customPricingFactory;
        $this->_priceCurrency = $priceCurrency;
        $this->taxHelper = $taxHelper;
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|BaseOriginalPrice
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return [
            'code' =>'base_original_subtotal',
            'title' => $this->getLabel(),
            'value' => $this->getOldPrice()
        ];
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('Base Original Subtotal');
    }

    /**
     * @return false|float|int
     */
    public function getOldPrice()
    {

        return $this->customPricingFactory->create()
                                          ->setCart($this->cart)
                                          ->getBaseOriginalPrice();
    }
}
