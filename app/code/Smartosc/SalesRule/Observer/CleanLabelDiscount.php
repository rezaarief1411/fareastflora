<?php

namespace Smartosc\SalesRule\Observer;

use Magento\Framework\Event\ObserverInterface;

class CleanLabelDiscount implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $quote->setData('label_discount', '');
    }
}
