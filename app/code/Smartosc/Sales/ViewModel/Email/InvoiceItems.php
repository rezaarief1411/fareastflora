<?php

namespace Smartosc\Sales\ViewModel\Email;

/**
 * Class InvoiceItems
 * @package Smartosc\Sales\ViewModel\Email
 */
class InvoiceItems implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_order;

    /**
     * @var \Smartosc\CustomBundleProduct\ViewModel\Adminhtml\OrderDetail
     */
    protected $orderDetail;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\ItemFactory
     */
    protected $invoiceItemFactory;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $repotService;

    /**
     * InvoiceItems constructor.
     *
     * @param \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $repotService
     */
    public function __construct(
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $repotService,
        \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory,
        \Smartosc\CustomBundleProduct\ViewModel\Adminhtml\OrderDetail $orderDetail
    ) {
        $this->orderDetail = $orderDetail;
        $this->invoiceItemFactory = $invoiceItemFactory;
        $this->repotService = $repotService;
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function isReportServiceProduct($productId)
    {
        return $this->repotService->isReportServiceProduct($productId);
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
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order\Invoice\Item
     */
    public function getFakeRepot()
    {
        $order = $this->getOrder();
        $orderDetail = $this->orderDetail
            ->setOrder($order);
        $orderItem = $orderDetail->getOrderItem();
        $invoiceItem = $this->invoiceItemFactory->create();
        $product = $this->repotService->getProduct();
        $invoiceItem->setOrderItem($orderItem);
        $invoiceItem->setProductId($product->getId());
        $invoiceItem->setName($product->getName());
        $invoiceItem->setSku($product->getSku());
        $invoiceItem->setPrice($product->getPrice());
        return $invoiceItem;
    }
}
