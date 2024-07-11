<?php
namespace Smartosc\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\OrderSender as DefaultOrderSender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Smartosc\Sales\Model\Custom\MoreAboutShippingDescription;
use Smartosc\Sales\Model\Custom\MoreAboutShippingDescriptionFactory;

/**
 * Class OrderSender
 * This class extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
 */
class OrderSender extends DefaultOrderSender
{
    CONST PAYPAL_METHOD_CODE = 'paypal_express';

    protected $moreAboutShippingDescriptionFactory;

    public function __construct(
        MoreAboutShippingDescriptionFactory $moreAboutShippingDescriptionFactory,
        Template $templateContainer,
        OrderIdentity $identityContainer,
        Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        $this->moreAboutShippingDescriptionFactory = $moreAboutShippingDescriptionFactory;
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $paymentHelper,
            $orderResource,
            $globalConfig,
            $eventManager
        );
    }


    /**
     * Prepare email template with variables
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {

        /** @var MoreAboutShippingDescription $moreInfoObject */
        $moreInfoObject = $this->moreAboutShippingDescriptionFactory->create();
        $transport = [
            'order' => $order,
            'order_id' => $order->getId(),
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'created_at_formatted' => $order->getCreatedAtFormatted(2),
            'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'is_not_virtual' => $order->getIsNotVirtual(),
                'email_customer_note' => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
            ],
            'moreAboutShippingDescription' => $moreInfoObject->info($order)
        ];
        $transportObject = new DataObject($transport);

        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
        $this->eventManager->dispatch(
            'email_order_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject, 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transportObject->getData());

        \Magento\Sales\Model\Order\Email\Sender::prepareTemplate($order);
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        $payment = $order->getPayment();
        $paymentHtml = $this->paymentHelper->getInfoBlockHtml(
            $payment,
            $this->identityContainer->getStore()->getStoreId()
        );

        if ($payment->getMethod() == self::PAYPAL_METHOD_CODE) {
            $paymentHtml = str_replace("</th>", ": ", str_replace("<th", "<td", str_replace("<td>", "", $paymentHtml)));
        }

        return $paymentHtml;
    }
}
