<?php

namespace Smartosc\Sales\Model\Custom;

/**
 * Class MessageForShippingType
 * @package Smartosc\Sales\Model\Custom
 */
class MessageForShippingType
{
    CONST DELIVERY_TIME = '12pm – 6pm';
    CONST PICKUP_STORE = 'in_store_pickup';
    CONST DELIVERY = 'delivery';

    /**
     * @var \Smartosc\Checkout\Helper\Order\Data
     */
    protected $helper;

    protected $checkoutHelperData;

    /**
     * MessageForShippingType constructor.
     * @param $helper
     */
    public function __construct(
        \Smartosc\Checkout\Helper\Order\Data $helper,
        \Smartosc\Checkout\Helper\Data $checkoutHelperData
    ) {
        $this->helper = $helper;
        $this->checkoutHelperData = $checkoutHelperData;
    }

    /**
     * @param $order
     * @return string
     */
    public function invoiceMessage($order)
    {
        $invoiceMessage = "";
        $shippingType   = $order->getShippingType(); // in_store_pickup | delivery

        if ($shippingType == self::PICKUP_STORE) {
            $pickupTime  = $order->getPickupTime();
            $pickupDate  = $this->helper->getDate($order->getPickupDate());

            $invoiceMessage .= __("Your collection has been scheduled on: %1, %2", $pickupDate, $pickupTime);
        } elseif ($shippingType == self::DELIVERY) {
            $deliveryDate = $this->helper->getDate($order->getDeliveryDate());

            $invoiceMessage .= __("Your delivery has been scheduled on: %1, %2", $deliveryDate, self::DELIVERY_TIME);
        }

        return $invoiceMessage;
    }

    /**
     * @param $order
     * @param $formatAddress
     * @return string
     */
    public function invoiceRecipientFormatted($order, $formatAddress)
    {
        $invoiceRecipientFormatted = "";
        $shippingType = $order->getShippingType(); // in_store_pickup | delivery

        if ($shippingType == self::PICKUP_STORE) {
            $billingAddress = $order->getBillingAddress();
            $billingName = $billingAddress->getPrefix() . ' ' . $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();

            $formatAddressArray = explode('<br />', $formatAddress);
            $formatAddressArray[0] = $billingName;

            $invoiceRecipientFormatted = implode('<br />', $formatAddressArray);

            return $invoiceRecipientFormatted;
        }

        return $formatAddress;
    }
}
