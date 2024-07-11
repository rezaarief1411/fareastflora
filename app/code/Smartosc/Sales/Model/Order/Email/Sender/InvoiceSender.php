<?php
namespace Smartosc\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender as DefaultInvoiceSender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Smartosc\Sales\Model\Custom\MoreAboutShippingDescription;
use Smartosc\Sales\Model\Custom\MoreAboutShippingDescriptionFactory;
use Smartosc\Sales\Model\Custom\MessageForShippingType;
use Smartosc\Sales\Model\Custom\MessageForShippingTypeFactory;
use Magento\Sales\Model\Order\Pdf\Invoice as InvoicePdfModel;

/**
 * Class InvoiceSender
 *
 */
class InvoiceSender extends DefaultInvoiceSender
{

    protected $moreAboutShippingDescriptionFactory;

    protected $messageForShippingTypeFactory;

    protected $invoicePdfModel;

    public function __construct(
        MoreAboutShippingDescriptionFactory $moreAboutShippingDescriptionFactory,
        MessageForShippingTypeFactory $messageForShippingTypeFactory,
        InvoicePdfModel $invoicePdfModel,
        Template $templateContainer,
        InvoiceIdentity $identityContainer,
        Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        InvoiceResource $invoiceResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        $this->moreAboutShippingDescriptionFactory = $moreAboutShippingDescriptionFactory;
        $this->messageForShippingTypeFactory = $messageForShippingTypeFactory;
        $this->invoicePdfModel = $invoicePdfModel;
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $invoiceResource, $globalConfig, $eventManager);
    }


    /**
     * {@inheritdoc}
     */
    public function send(Invoice $invoice, $forceSyncMode = false)
    {
        $invoice->setSendEmail($this->identityContainer->isEnabled());

        if (!$invoice->getEmailSent() && (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode)) {
            $order = $invoice->getOrder();
            $this->identityContainer->setStore($order->getStore());
            /**
             * @var MoreAboutShippingDescription $moreInfoObject
             */
            $moreInfoObject = $this->moreAboutShippingDescriptionFactory->create();
            /**
             * @var messageForShippingType $remindMessage
             */
            $remindMessage = $this->messageForShippingTypeFactory->create();
            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),
                'invoice' => $invoice,
                'invoice_id' => $invoice->getId(),
                'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $remindMessage->invoiceRecipientFormatted($order, $this->getFormattedShippingAddress($order)),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ],
                'moreAboutShippingDescription' => $moreInfoObject->info($order),
                'remindMessage' => $remindMessage->invoiceMessage($order)
            ];
            $transportObject = new DataObject($transport);

            /**
             * Event argument `transport` is @deprecated. Use `transportObject` instead.
             */
            $this->eventManager->dispatch(
                'email_invoice_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );

            $this->templateContainer->setTemplateVars($transportObject->getData());

            $pdfContent = $this->invoicePdfModel->getPdf([$invoice])->render();

            if ($pdfContent) {
                $this->templateContainer->setPdfContent($pdfContent);
            }

            if ($this->checkAndSend($order)) {
                $invoice->setEmailSent(true);
                $this->invoiceResource->saveAttribute($invoice, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $invoice->setEmailSent(null);
            $this->invoiceResource->saveAttribute($invoice, 'email_sent');
        }

        $this->invoiceResource->saveAttribute($invoice, 'send_email');

        return false;
    }
}
