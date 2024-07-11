<?php

namespace Smartosc\Sales\ViewModel;

/**
 * Class Totals
 * @package Smartosc\Sales\ViewModel
 */
class Totals implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_order;

    /**
     * @var \Smartosc\Checkout\Model\Quote\CustomPricingFactory
     */
    protected $customPricingFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Order constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
    \Smartosc\Checkout\Model\Quote\CustomPricingFactory $customPricingFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->customPricingFactory = $customPricingFactory;
        $this->quoteFactory = $quoteFactory;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @param \Magento\Sales\Model\Order $_order
     * @return $this
     */
    public function setOrder($_order)
    {
        $this->_order = $_order;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getOldPrice()
    {
        $customPricing = $this->getCustomPricingModel();
        return $customPricing->getBaseOriginalPrice();
    }

    protected function getCustomPricingModel() {
        $quoteId = $this->getOrder()->getQuoteId();
        $quote = $this->quoteFactory->create()->load($quoteId);
        $customPricing = $this->customPricingFactory->create();
        $customPricing->setCart($quote);

        return $customPricing;
    }
    /**
     * @return bool|string
     */
    public function getTotalSaving()
    {
        $customPricing = $this->getCustomPricingModel();
        return $customPricing->getTotalSaving();
    }
}
