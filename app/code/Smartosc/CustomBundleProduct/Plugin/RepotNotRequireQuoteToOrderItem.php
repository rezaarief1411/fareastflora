<?php

namespace Smartosc\CustomBundleProduct\Plugin;

class RepotNotRequireQuoteToOrderItem
{
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    )
    {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        if ($item->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $orderItem->setIsRepotNotRequire($item->getIsRepotNotRequire());
        }
        return $orderItem;
    }
}