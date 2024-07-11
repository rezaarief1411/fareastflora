<?php

namespace Smartosc\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class CustomerLogin
 * @package Smartosc\Customer\Observer
 */
class CustomerLogin implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * CustomerLogin constructor.
     * @param QuoteRepository $quoteRepository
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $floorAttribute = '';
        $buildingAttribute = '';
        if ($customer->getData('addresses')
            && $customer->getData('addresses')[0]
            && isset($customer->getData('addresses')[0]['custom_attributes'])
            && $customer->getData('addresses')[0]['custom_attributes']) {
            $customerAddressAttributes = $customer->getData('addresses')[0]['custom_attributes'];
            foreach ($customerAddressAttributes as $addressAttribute) {
                if ($addressAttribute['attribute_code'] == 'floor') {
                    $floorAttribute = $addressAttribute['value'];
                }
                if ($addressAttribute['attribute_code'] == 'building') {
                    $buildingAttribute = $addressAttribute['value'];
                }
            }
        }

        $quoteId = $this->getQouteId();
        if ($quoteId && $floorAttribute && $buildingAttribute) {
            $quote = $this->quoteRepository->get($quoteId);
            $quote->setData('billing_floor', $floorAttribute)
                ->setData('billing_building', $buildingAttribute);
            $this->quoteRepository->save($quote);
        }
        return ;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQouteId()
    {
        return (int)$this->checkoutSession->getQuote()->getId();
    }
}
