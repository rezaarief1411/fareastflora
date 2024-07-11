<?php

namespace Fef\CustomVoucherPoint\Observer;

use Psr\Log\LoggerInterface;
use Magento\Customer\Model\AddressFactory;
use Fef\CustomShipping\Model\ShippingInformationManagement;

/**
 * Class SavePickUpInOrder
 * @package Smartosc\Checkout\Observer
 */
class PlaceOrdeBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/cart-coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("PlaceOrdeBefore");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('\Magento\Customer\Model\Session');
        $customHelper = $objectManager->get('\Fef\CustomVoucherPoint\Helper\Data');
        $helperData = $objectManager->get('\Fef\CustomShipping\Helper\Data');
        $voucherPointFactory = $objectManager->get('\Fef\CustomVoucherPoint\Model\VoucherPointFactory');
        $voucherPointUsedFactory = $objectManager->get('\Fef\CustomVoucherPoint\Model\VoucherPointUsedFactory');
        $zokuRewardQuoteFactory = $objectManager->get('\Zoku\Rewards\Model\ResourceModel\Quote');

        $redeemUrl = $helperData->getConfig("carriers/custom/base_url")."loyalty/redeem";

        $order = $observer->getEvent()->getOrder();
        $customerId = $order->getCustomerId();
        $quoteId = $order->getQuoteId();

        $logger->info("customerId : $customerId || quoteId : $quoteId   ");

        $voucherPointUsedCollection = $voucherPointUsedFactory->create()
        ->getCollection()
        ->addFieldToFilter('customer_id', $customerId)
        ->addFieldToFilter('quote_id', $quoteId);
        $voucherUsedData = $voucherPointUsedCollection->getData();

        $usedVoucher = "";
        if(count($voucherUsedData) > 0 ){
            $usedVoucher = $voucherUsedData[0]["used_voucher"];
        }

        // $logger->info("usedVoucher : $usedVoucher");

        $voucherPoint = $voucherPointFactory->create()
            ->getCollection()
            ->addFieldToSelect(array("proseller_member_id","member_voucher_list"))
            ->addFieldToFilter('customer_id',array('eq' => $customerId));

        $listVoucherArr = $voucherPoint->getData();

        $memberId = "";
        $availVouchers = array();
        foreach ($listVoucherArr as $listVoucher) {
            $availVouchers = json_decode($listVoucher["member_voucher_list"],true);
            $memberId = $listVoucher["proseller_member_id"];
        }
        // $logger->info("memberId : $memberId");

        $serialNumberVoucher = "";
        if($usedVoucher!=""){
            foreach ($availVouchers as $availVoucher){
                if($availVoucher["id"]==$usedVoucher){
                    $serialNumberVoucher = $availVoucher["serialNumber"];
                }
            }
        }
        // $logger->info("serialNumberVoucher : $serialNumberVoucher");

        $zokuRewardQuoteCollection = $zokuRewardQuoteFactory->loadByQuoteId($quoteId);

        $usedPoints = 0;
        if(!empty($zokuRewardQuoteCollection)){
            $usedPoints = $zokuRewardQuoteCollection["reward_points"];
        }

        $hitParams = array(
            "customerId" => $memberId,
            "points" => $usedPoints,
            "reference" => $order->getIncrementId()
        );
        if($serialNumberVoucher!= ""){
            $hitParams["vouchers"] = array(
                array(
                    "id" => $usedVoucher,
                    "serialNumber" => $serialNumberVoucher
                )
            );
        }else{
            $hitParams["vouchers"] = array();
        }
        
        $logger->info("hitParamsRedeem : ".print_r($hitParams,true));
        $logger->info("redeemUrl : $redeemUrl");
        $logger->info(json_encode($hitParams));
        $applyResponse = $helperData->setCurl($redeemUrl,"POST",$hitParams,1);
        $logger->info("applyResponse : ".print_r($applyResponse,true));

        // $logger2->info("PlaceOrdeBefore");

        return $this;
    }
}
