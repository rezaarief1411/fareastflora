<?php

namespace Smartosc\Owebia\Model\Checkout\Model;

/**
 * Class TotalsInformationManagement
 *
 * @package Smartosc\Owebia\Model\Checkout\Model
 */
class TotalsInformationManagement extends \Magento\Checkout\Model\TotalsInformationManagement
{

    /**
     * @var \Magento\Quote\Model\ShippingMethodManagement
     */
    protected $shippingMethodManagement;
    /**
     * @var \Magento\Quote\Api\Data\EstimateAddressInterfaceFactory
     */
    protected $estimatedAddressFactory;

    /**
     * TotalsInformationManagement constructor
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
     * @param \Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement
     * @param \Magento\Quote\Api\Data\EstimateAddressInterfaceFactory $estimatedAddressFactory
     */
    public function __construct(\Magento\Quote\Api\CartRepositoryInterface              $cartRepository,
                                \Magento\Quote\Api\CartTotalRepositoryInterface         $cartTotalRepository,
                                \Magento\Quote\Model\ShippingMethodManagement           $shippingMethodManagement,
                                \Magento\Quote\Api\Data\EstimateAddressInterfaceFactory $estimatedAddressFactory)
    {
        parent::__construct($cartRepository, $cartTotalRepository);
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->estimatedAddressFactory = $estimatedAddressFactory;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function calculate($cartId, \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation)
    {
        $quote = $this->cartRepository->get($cartId);
        $this->validateQuote($quote);
        if (!empty($quote->getCustomerId()) && $quote->getCustomerId() !== 0 && !empty($quote->getShippingAddress()->getCustomerId()) && !empty($quote->getShippingAddress()->getCustomerAddressId())) {
            $shippingMethods = $this->shippingMethodManagement->estimateByAddressId($cartId, $quote->getShippingAddress()->getCustomerAddressId());
        } else {
            /** @var \Magento\Quote\Api\Data\EstimateAddressInterface $estimatedAddress */
            $estimatedAddress = $this->estimatedAddressFactory->create();
            $estimatedAddress->setCountryId("SG");
            $estimatedAddress->setPostcode(null);
            $shippingMethods = $this->shippingMethodManagement->estimateByAddress($cartId, $estimatedAddress);
        }
        if (!empty($shippingMethods) && is_array($shippingMethods)) {
            /** @var \Magento\Quote\Api\Data\ShippingMethodInterface $owebiaMethod */
            $owebiaMethod = array_filter(array_map(function ($shippingMethod) {
                if ($shippingMethod->getCarrierCode() == \Owebia\AdvancedShipping\Model\Carrier::CODE) {
                    return $shippingMethod;
                }
            }, $shippingMethods));
            $owebiaMethod = array_shift($owebiaMethod);
        }
        if ($quote->getIsVirtual()) {
            $quote->setBillingAddress($addressInformation->getAddress());
        } else {
            $quote->setShippingAddress($addressInformation->getAddress());
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $carrierCode = $addressInformation->getShippingCarrierCode();
            $methodCode = $addressInformation->getShippingMethodCode();
            // Re estimated address, the shipping carrier code has change the shipping method code
            if (!empty($owebiaMethod) && method_exists($owebiaMethod, 'getCarrierCode') && method_exists($owebiaMethod, 'getMethodCode') && !empty($owebiaMethod->getCarrierCode()) && !empty($owebiaMethod->getMethodCode()) && $carrierCode == $owebiaMethod->getCarrierCode() && $methodCode != $owebiaMethod->getMethodCode()) {
                $carrierCode = $owebiaMethod->getCarrierCode();
                $methodCode = $owebiaMethod->getMethodCode();
            }
            $quote->getShippingAddress()->setShippingMethod(
                $carrierCode . '_' . $methodCode
            );
        }
        $quote->collectTotals();

        return $this->cartTotalRepository->get($cartId);
    }
}
