<?php

namespace Smartosc\Sales\Observer\Order\Email;

/**
 * Class Gift
 * @package Smartosc\Sales\Observer\Order\Email
 */
class Gift implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $_transportObject;

    /**
     * @var \Smartosc\Sales\Block\Email\GiftMessage
     */
    protected $blockGiftMessage;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Gift constructor.
     * @param \Smartosc\Sales\Block\Email\GiftMessage $blockGiftMessage
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Smartosc\Sales\Block\Email\GiftMessage $blockGiftMessage,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->blockGiftMessage = $blockGiftMessage;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return mixed
     */
    public function getTransportObject()
    {
        return $this->_transportObject;
    }

    /**
     * @param mixed $transportObject
     */
    public function setTransportObject($transportObject)
    {
        $this->_transportObject = $transportObject;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (null === $this->_order) {
            $transportObject = $this->getTransportObject();
            if ($order = $transportObject->getOrder()) {
                $this->setOrder($order);
            } else {
                $orderId = $transportObject->getOrderId();
                $order = $this->orderRepository->get($orderId);
                $this->setOrder($order);
            }
        }
        return $this->_order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $transportObject */
        $transportObject = $observer->getData('transportObject');
        $this->setTransportObject($transportObject);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        $sender = get_class($observer->getEvent()->getSender()) ;
        $senders =  explode('\\',$sender);
        $transportObject->setFromSender($senders[count($senders) - 1]);
        if ($senders[count($senders) - 1] == "OrderSender") {
            $transportObject->setOrderEmail(1);
        } elseif ($senders[count($senders) - 1] == "InvoiceSender") {
            $transportObject->setInvoiceEmail(1);
        } else {
            $transportObject->setShipmentEmail(1);
        }
        if ($order) {
            $blockGiftMessage = $this->blockGiftMessage
                ->setOrder($order);

            if ($giftMessage = $blockGiftMessage->toHtml()) {
                $transportObject->setGiftMessage($giftMessage);
            }
        }
    }
}
