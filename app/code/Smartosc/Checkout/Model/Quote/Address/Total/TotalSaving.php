<?php

namespace Smartosc\Checkout\Model\Quote\Address\Total;

/**
 * Class TotalSaving
 * @package Smartosc\Checkout\Model\Quote\Address\Total
 */
class TotalSaving extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
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
     * TotalSaving constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Helper\Data $taxHelper
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Data $taxHelper,
        \Smartosc\Checkout\Model\Quote\CustomPricingFactory $customPricingFactory,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->taxHelper = $taxHelper;
        $this->cart = $cart;
        $this->customPricingFactory = $customPricingFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|TotalSaving
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
            'code' =>'total_saving',
            'title' => $this->getLabel(),
            'value' => $this->getTotalSaving()
        ];
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('Total Saving');
    }

    /**
     * @return false|float|int
     */
    public function getTotalSaving()
    {

        return $this->customPricingFactory->create()->setCart($this->cart)->getTotalSaving();
    }
}
