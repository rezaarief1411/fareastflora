<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fef\CustomVoucherPoint\Model\Quote\Address;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;

/**
 * Class ToOrder converter
 */
class ToOrder
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param OrderFactory $orderFactory
     * @param Copy $objectCopyService
     * @param ManagerInterface $eventManager
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderFactory $orderFactory,
        Copy $objectCopyService,
        ManagerInterface $eventManager,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderFactory = $orderFactory;
        $this->objectCopyService = $objectCopyService;
        $this->eventManager = $eventManager;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Address $object
     * @param array $data
     * @return OrderInterface
     */
    public function convert(Address $object, $data = [])
    {
        
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/cart-coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("convert");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $voucherCalculateTempFactory = $objectManager->get('\Fef\CustomVoucherPoint\Model\CalculateTempFactory');

        $orderData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_order',
            $object
        );

        $voucherCalculateTempCollection = $voucherCalculateTempFactory->create()
        ->getCollection()
        ->addFieldToSelect(array("calculate_result"))
        ->addFieldToFilter('customer_id', $orderData["customer_id"])
        ->addFieldToFilter('quote_id', $object->getQuote()->getId());
        $voucherCalculateTempData = $voucherCalculateTempCollection->getData();

        foreach ($voucherCalculateTempData as $voucherCalculateTemp) {
            $tempResultArr = json_decode($voucherCalculateTemp["calculate_result"],true);

            $logger->info("shipping_incl_tax : ".$orderData["shipping_incl_tax"]);
            // foreach ($tempResultArr as $tempResult) {
                $shippingAmount = $orderData["shipping_incl_tax"];
                $orderData["base_subtotal"] =  $tempResultArr['totalNettAmount'] + $shippingAmount;
                $orderData["subtotal"]= $tempResultArr['totalNettAmount'] + $shippingAmount;
                $orderData["discount_amount"]= $tempResultArr['totalDiscountAmount'] + $shippingAmount;
                $orderData["discount_invoiced"]= $tempResultArr['totalDiscountAmount'] + $shippingAmount;
                $orderData["base_discount_amount"]= $tempResultArr['totalDiscountAmount'] + $shippingAmount;
                $orderData["base_discount_invoiced"]= $tempResultArr['totalDiscountAmount'] + $shippingAmount;
                $orderData["grand_total"]= $tempResultArr['totalNettAmount'] + $shippingAmount;
                $orderData["base_grand_total"]= $tempResultArr['totalNettAmount'] + $shippingAmount;
            // }
        }

        // $logger2->info("orderData : ".print_r($orderData,true));
        // $logger->info("data : ".print_r($data,true));

        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $this->orderFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $order,
            array_merge($orderData, $data),
            \Magento\Sales\Api\Data\OrderInterface::class
        );
        $order->setStoreId($object->getQuote()->getStoreId())
            ->setQuoteId($object->getQuote()->getId())
            ->setIncrementId($object->getQuote()->getReservedOrderId());

        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote',
            'to_order',
            $object->getQuote(),
            $order
        );
        $this->eventManager->dispatch(
            'sales_convert_quote_to_order',
            ['order' => $order, 'quote' => $object->getQuote()]
        );
        return $order;
    }
}
