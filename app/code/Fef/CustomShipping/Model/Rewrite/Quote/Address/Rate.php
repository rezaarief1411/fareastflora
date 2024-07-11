<?php
 
namespace Fef\CustomShipping\Model\Rewrite\Quote\Address;

use \Magento\Checkout\Model\Session as CheckoutSession;

class Rate extends \Magento\Quote\Model\Quote\Address\Rate
{
    public function importShippingRate(\Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/reza-test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info("==================================================================");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $helperData = $objectManager->get('\Fef\CustomShipping\Helper\Data');
        $token = $helperData->generateToken();
        if($token==""){
            if($helperData->getDebugMode()==1){
                $logger->info("Failed get shipping rate. Generate auth token was failed");
            }
        }

        $costData = $this->callApi($rate);
        // $logger->info(print_r($costData,true));
        $finalPrice = 0;
        $methodDescription = "<br/>";
        if(!empty($costData)){
            $finalPrice = $costData["totalDeliveryCost"];
            $deliveryCostBreakdown = $costData["deliveryCostBreakdown"];
            foreach ($deliveryCostBreakdown as $key => $value) {
                $methodDescription.="$key:$value <br/>";
            }
        }

        
        

        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
            $this->setCode(
                $rate->getCarrier() . '_error'
            )->setCarrier(
                $rate->getCarrier()
            )->setCarrierTitle(
                $rate->getCarrierTitle()
            )->setErrorMessage(
                $rate->getErrorMessage()
            );
        } elseif ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $this->setCode(
                $rate->getCarrier() . '_' . $rate->getMethod()
            )->setCarrier(
                $rate->getCarrier()
            )->setCarrierTitle(
                $rate->getCarrierTitle()
            )->setMethod(
                $rate->getMethod()
            )->setMethodTitle(
                $rate->getMethodTitle()
            )->setMethodDescription(
                $rate->getMethodDescription()
            )->setPrice(
                $finalPrice
            );
            // $this->setData("aa",1);
        }
        return $this;
    }

    private function callApi($rate)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/shipping-rate.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
        $helperData = $objectManager->get('\Fef\CustomShipping\Helper\Data');

        $checkoutQuote = $checkoutSession->getQuote();
        $checkoutQuoteId = $checkoutQuote->getId();
        $checkoutQuoteAddress = $checkoutQuote->getShippingAddress();
        $checkoutQuoteAddressShipping = str_replace("custom_","",$checkoutQuoteAddress->getShippingMethod());
        if($checkoutQuoteAddressShipping == "standart"){
            $checkoutQuoteAddressShipping = "standard";
        }
        $deliveryStairs = $checkoutQuote->getData('delivery_stairs');
        
        $deliverySlot = $checkoutQuote->getData('delivery_timeslot');
        $deliveryDate = $checkoutQuote->getData('delivery_date');

        if($checkoutQuoteId!=0){
            // //CALL SHIPPING RATE API
            $allItems = $checkoutQuote->getAllVisibleItems();
            $paramItems = [];
            $k = 0;
            foreach ($allItems as $item) {
                $productObject = $objectManager->create('Magento\Catalog\Model\Product');
                $item_s = $productObject->load($item->getProductId());
                $paramItems[$k] = array(
                    "id" => $item_s->getProsellerId(),
                    "weight" => (float)$item->getWeight() * $item->getQty(),
                    "quantity" => $item->getQty()
                );
                $k++;
            }

            $deliveryTime = "";
            if($deliverySlot){
                $deliverySlotArr = explode(" - ",$deliverySlot);
                $deliveryTime = $deliverySlotArr[0];
            }

            $apiParams = array(
                "postalCode" => $checkoutQuoteAddress->getPostcode(),
                "datetime" => $newDate = date("Y-m-d", strtotime($deliveryDate))." ".$deliveryTime,
                "others" => array(
                    "staircase" => (int)$deliveryStairs,
                    "deliveryType" => $checkoutQuoteAddressShipping
                ),
                "items" => $paramItems,
            );
            if($helperData->getDebugMode()==1){
                // $logger->info("url : ".$helperData->getUrl("rate"));
                // $logger->info("apiParams : ".json_encode($apiParams));
            }

            
            // $modelFefResultFactory = $objectManager->get('\Fef\CustomShipping\Model\FefRateResultFactory');
            // $modelFefResult = $modelFefResultFactory->create();
            // $collectionResult = $modelFefResult->load($checkoutQuoteId, 'quote_id');
            // $dataCollectionResult = $collectionResult->getData();

            // $countData = count($collectionResult->getData());
            // if($countData==0){
                $resGetRateResult = $helperData->setCurl(
                    $helperData->getUrl("rate"),
                    "POST",
                    $apiParams,
                    1
                );

                if($helperData->getDebugMode()==1){
                    $logger->info($resGetRateResult);
                }
                
                // $modelFefResult->setQuoteId($checkoutQuoteId);
                // $modelFefResult->setRateResultShipping($checkoutQuoteAddressShipping);
                // $modelFefResult->setApiParams(json_encode($apiParams));
                // $modelFefResult->setApiResult($resGetRateResult);
                // $modelFefResult->save();

                $resGetRateResultArray = json_decode($resGetRateResult,true);
                // $logger->info("resGetRateResultArray : ".print_r($resGetRateResultArray,true));
                if($resGetRateResultArray["status"]=="success"){
                    $rateData = $resGetRateResultArray["data"];
                    $this->setAdditionalCost($checkoutQuote,$rateData["deliveryCostBreakdown"]);
                    // return $rateData["totalDeliveryCost"];
                    return $rateData;
                } else {                    
                    return [];
                }

            // } else {
            //     // $logger->info($checkoutQuoteAddressShipping ." || ".$collectionResult["rate_result_shipping"] ." (".$collectionResult["id"].")");
            //     if($checkoutQuoteAddressShipping != $collectionResult["rate_result_shipping"]){
            //         $resGetRateResult = $helperData->setCurl(
            //             $helperData->getUrl("rate"),
            //             "POST",
            //             $apiParams,
            //             1
            //         );
            //         $modelFefResult->setRateResultShipping($rate->getMethod());
            //         $modelFefResult->setApiParams(json_encode($apiParams));
            //         $modelFefResult->setApiResult($resGetRateResult);
            //         $modelFefResult->save();

            //         $resGetRateResultArray = json_decode($resGetRateResult,true);
            //         if(isset($resGetRateResultArray["data"]["totalDeliveryCost"]) && $resGetRateResultArray["data"]["totalDeliveryCost"]!=""){
            //             return $resGetRateResultArray["data"]["totalDeliveryCost"];
            //         }
            //     } else{
            //         $apiResult = json_decode($dataCollectionResult["api_result"],true);
            //         if($apiResult["status"]=="success"){
            //             return $apiResult["data"]["totalDeliveryCost"];
            //         }else {
            //             $logger->info("Failed get shipping rate !");
            //             return 0;
            //         }
            //     }
            // }
        }
    }

    private function setAdditionalCost($quote,$apiResult)
    {
        if(isset($apiResult["weight"])){
            $quote->setData("cost_weight", $apiResult["weight"]);
        }else{
            $quote->setData("cost_weight", 0);
        }
        if(isset($apiResult["location"])){
            $quote->setData("cost_location", $apiResult["location"]);
        }else{
            $quote->setData("cost_location", 0);
        }
        if(isset($apiResult["itemSpecific"])){
            $quote->setData("cost_item_spesific", $apiResult["itemSpecific"]);
        }else{
            $quote->setData("cost_item_spesific", 0);
        }
        if(isset($apiResult["period"])){
            $quote->setData("cost_period", $apiResult["period"]);
        }else{
            $quote->setData("cost_period", 0);
        }
        if(isset($apiResult["staircase"])){
            $quote->setData("cost_staircase", $apiResult["staircase"]);
        }else{
            $quote->setData("cost_staircase", 0);
        }
        if(isset($apiResult["deliveryType"])){
            $quote->setData("cost_delivery_type", $apiResult["deliveryType"]);
        }else{
            $quote->setData("cost_delivery_type", 0);
        }
        $quote->save();
    }

}