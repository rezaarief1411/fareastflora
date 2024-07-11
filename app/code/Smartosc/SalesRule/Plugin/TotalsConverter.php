<?php

namespace Smartosc\SalesRule\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ExtendConfigProvider
 * @package Smartosc\Checkout\Plugin
 */
class TotalsConverter
{
    const BASE_ORIGINAL_SUBTOTAL = 'base_original_subtotal';
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $checkoutSession;
    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * TotalsConverter constructor.
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function __construct(
        Session $checkoutSession,
        Json $jsonSerializer
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param $addressTotals
     * @param $result
     * @return mixed
     */
    public function afterProcess(\Magento\Quote\Model\Cart\TotalsConverter $subject, $addressTotals, $result)
    {
        /**
         * @var \Magento\Quote\Api\Data\TotalSegmentInterface $totalSegment
         */
        foreach ($result as $code => $totalSegment) {
            if ($code == self::BASE_ORIGINAL_SUBTOTAL) {
                $quote = $this->checkoutSession->getQuote();
                $discountJson = $quote->getData('label_discount');
                if (!empty($discountJson)){
                    $discount = $this->jsonSerializer->unserialize($discountJson);
                    if (!empty($discount) && is_array($discount)){
                        foreach ($discount as $index => $item){
                            if (isset($item['discount']) && $item['discount'] == 0){
                                unset($discount[$index]);
                            }
                        }
                        $discountJson = $this->jsonSerializer->serialize($discount);
                    }
                }
                $totalSegment->setTitle($discountJson);
            }
            $result[$code] = $totalSegment;
        }
        return $result;
    }
}
