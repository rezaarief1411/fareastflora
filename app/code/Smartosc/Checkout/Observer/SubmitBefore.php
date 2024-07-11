<?php

namespace Smartosc\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Smartosc\Checkout\Logger\Logger;

class SubmitBefore implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $_customLogger;

    /**
     * @param Logger $_customLogger
     */
    public function __construct(Logger $_customLogger)
    {
        $this->_customLogger = $_customLogger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quoteId = $quote->getId();
        $createAt = $quote->getCreatedAt();
        $deliveryDate = $quote->getDeliveryDate();
        $this->_customLogger->info('Quote Id: ' . $quoteId);
        $this->_customLogger->info('Quote created at: ' . $createAt);
        $this->_customLogger->info('Delivery date when creating the quote: ' . $deliveryDate);
    }
}
