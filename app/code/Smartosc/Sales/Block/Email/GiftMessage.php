<?php

namespace Smartosc\Sales\Block\Email;

/**
 * Class GiftMessage
 * @package Smartosc\Sales\Block\Email
 */
class GiftMessage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    /**
     * @var string
     */
    protected $_template;

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
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

    public function toHtml()
    {
        $_order = $this->getOrder();
        if (!$_order) {
            return '';
        }
        $giftMessageFrom = $_order->getGiftMessageFrom();
        $giftMessageTo = $_order->getGiftMessageTo();
        $giftMessage = $_order->getGiftMessage();
        $template = $this->getTemplate();

        if (null === $template) {
            $html = '';

            if ($giftMessageTo) {
                $html .= __('To: %1', $giftMessageTo) . "\n";
            } else {
                $html .= __('To: %1', 'Nil') . "\n";
            }

            if ($giftMessage) {
                $html .= __('Message: %1', $giftMessage) . "\n";
            } else {
                $html .= __('Message: %1', 'Nil') . "\n";
            }

            if ($giftMessageFrom) {
                $html .= __('From: %1', $giftMessageFrom);
            } else {
                $html .= __('From: %1', 'Nil');
            }

            return $html;
        } else {
            $this->setData('gift', [
                'from' => $giftMessageFrom,
                'to' => $giftMessageTo,
                'message' => $giftMessage
            ]);
            return parent::toHtml();
        }
    }
}
