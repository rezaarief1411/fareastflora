<?php

namespace Fef\CustomVoucherPoint\Observer;

use Psr\Log\LoggerInterface;
use Magento\Customer\Model\AddressFactory;

class PlaceOrderAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/cart-coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("PlaceOrdeAfter");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Fef\CustomVoucherPoint\Helper\Data');
        $order = $observer->getEvent()->getOrder();

        // $logger->info("Subtotal : ".$order->getSubtotal() ." || ".$order->getIncrementid() ." || ".$order->getQuoteId() );

        // $helper->removeUsedVoucherFromList($order->getCustomerId(),$order->getQuoteId());
        // $helper->clearUsedVoucherCustomer($order->getCustomerId());

        return $this;
    }
}
