<?php

namespace Smartosc\SalesRule\Observer;

use Magento\Framework\Event\ObserverInterface;

class ApplyRule implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $labelDiscountArr = [];
        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $observer->getRule();
        /**
         * @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
         */
        $discountData = $observer->getResult();
        /**
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $observer->getQuote();
        $labelDiscountJson = $quote->getData('label_discount');
        if ($labelDiscountJson) {
            $labelDiscountArr = json_decode($labelDiscountJson, true);
            if (isset($labelDiscountArr[$rule->getId()])) {
                $discount = $labelDiscountArr[$rule->getId()]['discount'];
                $labelDiscountArr[$rule->getId()]['discount'] = $discount + $discountData->getAmount();
            } else {
                $labelDiscountArr[$rule->getId()]['discount'] =  $discountData->getAmount();
            }
        } else {
            $labelDiscountArr[$rule->getId()]['discount'] =  $discountData->getAmount();
        }
        $labelDiscountArr[$rule->getId()]['title'] =  $rule->getStoreLabel();
        $quote->setData('label_discount', json_encode($labelDiscountArr));
    }
}
