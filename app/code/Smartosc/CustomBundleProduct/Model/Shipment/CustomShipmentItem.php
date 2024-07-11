<?php

namespace Smartosc\CustomBundleProduct\Model\Shipment;

/**
 * Class CustomShipmentItem
 * @package Smartosc\CustomBundleProduct\Model\Shipment
 */
class CustomShipmentItem
{
    /**
     * @var int
     */
    protected $_item_id;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\ItemFactory
     */
    protected $shipmentItemFactory;

    /**
     * CustomShipmentItem constructor.
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory
    ) {
        $this->shipmentItemFactory = $shipmentItemFactory;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * @param int $item_id
     * @return $this
     */
    public function setItemId($item_id)
    {
        $this->_item_id = $item_id;

        return $this;
    }

    /**
     * @return mixed
     */
    protected function prepareCollection()
    {
        return $this->orderItemFactory->create()
            ->getResourceCollection()
            ->addFieldToFilter(
                'parent_item_id',
                ['eq'=> $this->_item_id]
            );
    }

    /**
     * @return \Magento\Sales\Model\Order\Shipment\Item[]
     */
    public function getChildShipmentItems()
    {
        $result = [];
        $collection = $this->prepareCollection();
        foreach ($collection as $itemId => $orderItemObject) {
            $result[] = $this->shipmentItemFactory->create()->addData([
                'entity_id' => null,
                'parent_id' => null,
                'row_total' => $orderItemObject->getRowTotal(),
                'price' => $orderItemObject->getPrice(),
                'weight' => $orderItemObject->getWeight(),
                'qty' => $orderItemObject->getQtyOrdered(),
                'product_id' => $orderItemObject->getProductId(),
                'order_item_id' => $itemId,
                'name' => $orderItemObject->getName(),
                'sku' => $orderItemObject->getSku()
            ]);
        }

        return $result;
    }
}