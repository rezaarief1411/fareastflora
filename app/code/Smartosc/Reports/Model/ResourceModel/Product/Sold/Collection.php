<?php
namespace Smartosc\Reports\Model\ResourceModel\Product\Sold;

/**
 * class Collection
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
{
    /**
     * Add Ordered Qty
     *
     * @param string $from
     * @param string $to
     * @return $this|Collection
     */
    public function addOrderedQty($from = '', $to = '')
    {
        $connection = $this->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');

        $orderJoinCondition = [
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $connection->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        if ($from != '' && $to != '') {
            $fieldName = $orderTableAliasName . '.created_at';
            $orderJoinCondition[] = $this->prepareBetweenSql($fieldName, $from, $to);
        }

        $this->getSelect()->reset()->from(
            ['order_items' => $this->getTable('sales_order_item')],
            [
                'ordered_qty' => 'order_items.qty_ordered',
                'order_items_name' => 'order_items.name',
                'order_items_sku' => 'order_items.sku'
            ]
        )->joinInner(
            ['order' => $this->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            []
        )->where(
            'order_items.product_type = ?', 'simple'
        )->having(
            'order_items.qty_ordered > ?',
            0
        )->columns(
            'SUM(order_items.qty_ordered) as ordered_qty'
        )->group(
            'order_items.sku'
        );
        return $this;
    }
}
