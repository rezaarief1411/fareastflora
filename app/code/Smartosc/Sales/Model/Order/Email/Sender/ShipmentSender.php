<?php
namespace Smartosc\Sales\Model\Order\Email\Sender;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender as DefaultShipmentSender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilder;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Smartosc\Sales\Model\Custom\MoreAboutShippingDescriptionFactory;

/**
 * Class ShipmentSender
 *
 */
class ShipmentSender extends DefaultShipmentSender
{
	const ENABLE_SEND_SHIPMENT_EMAIL = 'sales_email/shipment/enabled';

	const ENABLE_ONLY_SEND_SHIPMENT_EMAIL_TO_SELLER = 'sales_email/shipment/only_cc';

	/**
	 * @var MoreAboutShippingDescriptionFactory
	 */
    protected $moreAboutShippingDescriptionFactory;

	/**
	 * @var ScopeConfigInterface
	 */
	protected $scopeConfig;

    public function __construct(
        MoreAboutShippingDescriptionFactory $moreAboutShippingDescriptionFactory,
        Template $templateContainer,
        ShipmentIdentity $identityContainer,
        Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        ShipmentResource $shipmentResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moreAboutShippingDescriptionFactory = $moreAboutShippingDescriptionFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $shipmentResource, $globalConfig, $eventManager);
    }


    public function send(Shipment $shipment, $forceSyncMode = false)
    {
        $shipment->setSendEmail($this->identityContainer->isEnabled());

        if (!$shipment->getEmailSent() && (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode)) {
            $order = $shipment->getOrder();
            $this->identityContainer->setStore($order->getStore());

            /**
             * @var MoreAboutShippingDescription $moreInfoObject
             */
            $moreInfoObject = $this->moreAboutShippingDescriptionFactory->create();

            $transport = [
                'order' => $order,
                'shipment' => $shipment,
                'comment' => $shipment->getCustomerNoteNotify() ? $shipment->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
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
                'email_shipment_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );

            $this->templateContainer->setTemplateVars($transportObject->getData());

            if ($this->checkAndSend($order)) {
                $shipment->setEmailSent(true);
                $this->shipmentResource->saveAttribute($shipment, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $shipment->setEmailSent(null);
            $this->shipmentResource->saveAttribute($shipment, 'email_sent');
        }

        $this->shipmentResource->saveAttribute($shipment, 'send_email');

        return false;
    }

	/**
	 * @param Order $order
	 * @return bool
	 */
    public function checkAndSend(Order $order)
    {
	    $enableOnlySendShipmentEmailToSeller = $this->checkEnableOnlySendShipmentEmailToSeller();
	    if ($enableOnlySendShipmentEmailToSeller){
		    $this->identityContainer->setStore($order->getStore());

		    if (!$this->identityContainer->isEnabled()) {
			    return false;
		    }
		    $this->prepareTemplate($order);

		    /** @var SenderBuilder $sender */
		    $sender = $this->getSender();

		    if ($this->identityContainer->getCopyMethod() == 'copy') {
			    try {
				    $sender->sendCopyTo();
			    } catch (\Exception $e) {
				    $this->logger->error($e->getMessage());
			    }
		    }
		    return true;
	    }
	    return parent::checkAndSend($order);
    }

	/**
	 * @return bool
	 */
	public function checkEnableOnlySendShipmentEmailToSeller()
	{
		$enableSendShipmentEmail = $this->scopeConfig->getValue(self::ENABLE_SEND_SHIPMENT_EMAIL);
		$enableOnlySendShipmentEmailToSeller = $this->scopeConfig->getValue(self::ENABLE_ONLY_SEND_SHIPMENT_EMAIL_TO_SELLER);
		return $enableSendShipmentEmail && $enableOnlySendShipmentEmailToSeller;
	}
}
