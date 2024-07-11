<?php

namespace Smartosc\Checkout\Observer\Quote;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

class SubmitObserver implements ObserverInterface
{
    const AUTO_CREATE_INVOICE = 'smartosc_general/auo_create_invoice/enable';
    const PATH_DISABLE_CREATE_INVOICE = 'smartosc_general/email_create_invoice/payment';
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;


    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * SubmitObserver constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender
    )
    {

        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var  Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();
        /**
         * a flag to set that there will be redirect to third party after confirmation
         */
        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
        if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
                /**
                 * Start override
                 * Custom disable send mail invoice when method action is SALE and payment methos is Paypal
                 */
                if ($this->disSendMailInvoicePaypal($order)) {
                    return;
                }
                /**
                 * End override
                 */
                $invoice = current($order->getInvoiceCollection()->getItems());
                if ($invoice) {
                    $this->invoiceSender->send($invoice);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
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

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     */
    private function disSendMailInvoicePaypal($order)
    {
        $methodCodePaypal = $this->getPaymentMethodPaypal();
        if ($order->getPayment()->getMethod() == $methodCodePaypal &&
            $this->getPaymentActionPaypal() == \Magento\Paypal\Model\AbstractConfig::PAYMENT_ACTION_SALE
        ) {
            return true;
        }
        return false;
    }
}
