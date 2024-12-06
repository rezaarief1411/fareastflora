<?php

namespace Smartosc\Checkout\Observer;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;

class CreateInvoice implements ObserverInterface
{
    const AUTO_CREATE_INVOICE = 'smartosc_general/auo_create_invoice/enable';
    const PATH_DISABLE_CREATE_INVOICE = 'smartosc_general/email_create_invoice/payment';
    const NETS_METHOD_CODE = 'netspayment';

    /**
     * @var CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Logging instance
     * @var \Smartosc\Checkout\Logger\Logger
     */
    protected $_customLogger;

    /**
     * @var SalesData
     */
    protected $salesData;

    /**
     * @param CollectionFactory $invoiceCollectionFactory
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param InvoiceSender $invoiceSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $invoiceCollectionFactory,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        InvoiceSender $invoiceSender,
        LoggerInterface $logger,
        \Smartosc\Checkout\Logger\Logger $_customLogger,
        SalesData $salesData
    ) {
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        $this->_customLogger = $_customLogger;
        $this->salesData = $salesData;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->disableCreateInvoiceCustomPaypal($observer->getEvent()->getOrder())) {
            return;
        }

        $order = $observer->getEvent()->getOrder();

        if ($this->enableAutoCreateInvoice()) {
            if ($order->getPayment()->getMethod() == self::NETS_METHOD_CODE) {
                return;
            }

            $orderId = $order->getId();
            $this->createInvoice($orderId);
        }

        $orderId = $order->getId();
        $createAt = $order->getCreatedAt();
        $deliveryDate = $order->getDeliveryDate();
        $this->_customLogger->info('OrderId: ' . $orderId);
        $this->_customLogger->info('Order created at: ' . $createAt);
        $this->_customLogger->info('Delivery date when creating the order: ' . $deliveryDate);
        return;
    }

    /**
     * @param $orderId
     * @return InvoiceInterface|Invoice|null
     * @throws LocalizedException
     */
    protected function createInvoice($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                if (!$order->canInvoice()) {
                    $this->logger->info('Order ' . $order->getIncrementId() . ' can not Invoice.');
                    return null;
                }
                $invoices = $this->invoiceCollectionFactory->create()
                    ->addAttributeToFilter('order_id', ['eq' => $order->getId()]);
                if ((int)$invoices->count() == 0) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                    $invoice->register();
                    $invoice->getOrder()->setCustomerNoteNotify(false);
                    $invoice->getOrder()->setIsInProcess(true);
                    $order->addStatusHistoryComment(__('Automatically INVOICED'), false);
                    $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();
                    $this->logger->info('Create invoice for order ' . $order->getIncrementId() . ' successfully.');
                    $invoice->setDiscountAmount($order->getDiscountAmount(0));
                    try {
                        if ($this->salesData->canSendNewInvoiceEmail()) {
                            $invoice->setSendEmail(true);
                            $this->invoiceSender->send($invoice);
                            $invoice->setEmailSent(true);
                            $invoice->save();
                        }
                    } catch (\Exception $e) {
                        $invoice->setSendEmail(null);
                        $invoice->setEmailSent(null);
                        $this->logger->error(__('We can\'t send the invoice email right now.'));
                        $this->logger->critical($e);
                        $invoice->save();
                    }
                    return $invoice;
                }
            }
        } catch (Exception $e) {
            throw new LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * @return mixed
     */
    public function enableAutoCreateInvoice()
    {
        return $this->scopeConfig->getValue(self::AUTO_CREATE_INVOICE);
    }

    /**
     * @return mixed
     */
    public function getPaymentMethodPaypal()
    {
        return $this->scopeConfig->getValue(self::PATH_DISABLE_CREATE_INVOICE);
    }

    /**
     * @return mixed
     */
    public function getPaymentActionPaypal()
    {
        return $this->scopeConfig->getValue('payment/paypal_express/payment_action');
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function disableCreateInvoiceCustomPaypal($order)
    {
        $methodCodePaypal= $this->getPaymentMethodPaypal();
        if ($order->getPayment()->getMethod() == $methodCodePaypal &&
            $this->getPaymentActionPaypal() == \Magento\Paypal\Model\AbstractConfig::PAYMENT_ACTION_SALE
        ) {
            return true;
        }
        return false;
    }
}
