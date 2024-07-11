<?php

namespace Smartosc\Checkout\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class InvoiceTrigger implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    const AUTO_CREATE_INVOICE = 'smartosc_general/auo_create_invoice/enable';

    /**
     * @param CollectionFactory $invoiceCollectionFactory
     * @param InvoiceSender $invoiceSender
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $invoiceCollectionFactory,
        InvoiceSender $invoiceSender,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->invoiceSender = $invoiceSender;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        $autoInvoice = $this->scopeConfig->getValue(self::AUTO_CREATE_INVOICE);

        if ($autoInvoice) {
            try {
                if ($order) {
                    $invoices = $this->invoiceCollectionFactory->create()->addAttributeToFilter('order_id', ['eq' => $order->getId()]);

                    foreach ($invoices as $key => $invoice) {
                        $this->sendInvoiceEmail($invoice);
                        $this->logger->info('Trigger invoice for order ' . $order->getId() . ' successfully.');
                    }
                }
            } catch (Exception $e) {
                throw new LocalizedException(
                    __($e->getMessage())
                );
            }
        }
    }

    /**
     * @param $invoice
     * @throws LocalizedException
     */
    protected function sendInvoiceEmail($invoice)
    {
        try {
            $this->invoiceSender->send($invoice);
        } catch (Exception $e) {
            throw new LocalizedException(
                __($e->getMessage())
            );
        }
    }
}