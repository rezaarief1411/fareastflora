<?php

namespace Smartosc\CustomBundleProduct\ViewModel\Adminhtml;

/**
 * Class OrderDetail
 * @package Smartosc\CustomBundleProduct\ViewModel\Adminhtml
 */
class OrderDetail implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem
     */
    protected $toOrderItem;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $repotService;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_order;

    /**
     * @var int
     */
    private $_parentItemId;

    /**
     * OrderDetail constructor.
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\Quote\ItemFactory $itemFactory
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $toOrderItem
     * @param \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $repotService
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
        \Magento\Quote\Model\Quote\Item\ToOrderItem $toOrderItem,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $repotService
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->itemFactory = $itemFactory;
        $this->toOrderItem = $toOrderItem;
        $this->repotService = $repotService;
    }

    /**
     * @return int
     */
    public function getParentItemId()
    {
        return $this->_parentItemId;
    }

    /**
     * @param int $parentItemId
     * @return $this
     */
    public function setParentItemId($parentItemId)
    {
        $this->_parentItemId = $parentItemId;
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return OrderDetail
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderItemInterface|bool
     */
    public function getOrderItem()
    {
            $order = $this->getOrder();
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            $item = $this->itemFactory->create();
            $item->setQuote($quote);
            $repot = $this->repotService->getProduct();
            $item->setProduct($repot);
            $orderItem = $this->toOrderItem->convert($item);

            $attributesManualUpdate = [
                'item_id' => 'mock_oi',
                'order_id' => $order->getId(),
                'parent_item_id' => $this->getParentItemId() ?? null,
                'quote_item_id' => 'mock_qi',
                'sku' => $repot->getSku(),
                'name' => ' [Disabled] ' . $repot->getName(),
                'qty_canceled' => 0,
                'qty_invoiced' => 0,
                'qty_refunded' => 0,
                'qty_shipped' => 0,
                'tax_invoiced' => 0,
                'base_tax_invoiced' => 0,
                'discount_invoiced' => 0,
                'base_discount_invoiced' => 0,
                'amount_refunded' => 0,
                'base_amount_refunded' => 0,
                'row_invoiced' => $repot->getFinalPrice(),
                'base_row_invoiced' => $repot->getFinalPrice(),
                'ext_order_item_id' => null,
                'locked_do_invoice' => null,
                'locked_do_ship' => null,
                'discount_tax_compensation_invoiced' => null,
                'base_discount_tax_compensation_invoiced' => null,
                'discount_tax_compensation_refunded' => null,
                'base_discount_tax_compensation_refunded' => null,
                'tax_canceled' => null,
                'discount_tax_compensation_canceled' => null,
                'tax_refunded' => null,
                'base_tax_refunded' => null,
                'discount_refunded' => null,
                'base_discount_refunded' => null,
                'price' => $repot->getFinalPrice(),
                'base_price' => $repot->getFinalPrice(),
                'original_price' => $repot->getPrice(),
                'base_original_price' => $repot->getPrice(),
                'row_total' => 0,
                'base_row_total' => 0
            ];

            foreach ($attributesManualUpdate as $key => $value) {
                $orderItem->setData($key, $value);
            }
            return $orderItem;

    }

    /**
     * @param int $productId
     * @return bool
     */
    public function isReportServiceProduct($productId)
    {
        return $this->repotService->isReportServiceProduct($productId);
    }
}
