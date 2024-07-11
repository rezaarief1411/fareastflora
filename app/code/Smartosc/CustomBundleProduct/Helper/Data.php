<?php

namespace Smartosc\CustomBundleProduct\Helper;

/**
 * Class Data
 * @package Smartosc\CustomBundleProduct\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONF_PRODUCT_ID = 'mpanel/conf_report_service/product_id';
    /**
     * @var \Smartosc\CustomBundleProduct\Model\Shipment\CustomShipmentItem
     */
    protected $customShipmentItem;
    /**
     * @var \Smartosc\CustomBundleProduct\ViewModel\Adminhtml\OrderDetail
     */
    protected $orderDetail;
    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $repotService;
    /**
     * @var \Magento\Sales\Model\Order\Invoice\ItemFactory
     */
    protected $invoiceItemFactory;
    /**
     * @var  \Magento\Sales\Model\Order\Shipment\ItemFactory $shippingItemFactory,
     */
    protected $shippingItemFactory;
    /**
     * Data constructor.
     * @param \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory
     * @param \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $repotService
     * @param \Smartosc\CustomBundleProduct\ViewModel\Adminhtml\OrderDetail $orderDetail
     * @param \Smartosc\CustomBundleProduct\Model\Shipment\CustomShipmentItem $customShipmentItem
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Sales\Model\Order\Shipment\ItemFactory $shippingItemFactory,
        \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $repotService,
        \Smartosc\CustomBundleProduct\ViewModel\Adminhtml\OrderDetail $orderDetail,
        \Smartosc\CustomBundleProduct\Model\Shipment\CustomShipmentItem $customShipmentItem,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->shippingItemFactory = $shippingItemFactory;
        $this->invoiceItemFactory = $invoiceItemFactory;
        $this->repotService = $repotService;
        $this->orderDetail = $orderDetail;
        $this->customShipmentItem = $customShipmentItem;
        parent::__construct($context);
    }

    /**
     * @param int $id Order Item id
     * @return \Magento\Sales\Model\Order\Shipment\Item[]
     */
    public function getChildShipmentItems($id)
    {
        return $this->customShipmentItem->setItemId($id)->getChildShipmentItems();
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function isReportServiceProduct($productId) {
        $config = $this->scopeConfig->getValue(self::CONF_PRODUCT_ID);

        return !$config || $productId == $config;
    }

    /**
     * @return mixed|null
     */
    public function getReportServiceProductId(){
        $reportServiceProductId = $this->scopeConfig->getValue(self::CONF_PRODUCT_ID);
        return $reportServiceProductId ? $reportServiceProductId : null;
    }

    /**
     * @param $order
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFakeRepotInvoice($order)
    {
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

    public function getFakeRepotShipping($order)
    {
        $orderDetail = $this->orderDetail->setOrder($order);
        $orderItem = $orderDetail->getOrderItem();
        $shippingItem = $this->shippingItemFactory->create();
        $product = $this->repotService->getProduct();
        $shippingItem->setOrderItem($orderItem);
        $shippingItem->setProductId($product->getId());
        $shippingItem->setName($product->getName());
        $shippingItem->setSku($product->getSku());
        $shippingItem->setPrice($product->getPrice());
        return $shippingItem;
    }
}
