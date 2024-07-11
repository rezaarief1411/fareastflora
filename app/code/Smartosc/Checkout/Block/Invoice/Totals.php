<?php

namespace Smartosc\Checkout\Block\Invoice;

use Magento\Framework\DataObject;
use Magento\Sales\Block\Order\Invoice\Totals as InvoiceTotals;

/**
 * Class Totals
 * @package Smartosc\Checkout\Block\Invoice
 */
class Totals extends InvoiceTotals
{
    /**
     * {@inheritDoc}
     */
    protected function _initTotals()
    {
        $result = parent::_initTotals();
        $source = $this->getSource();

        $this->addTotal(new DataObject([
            'code' => 'grand_total_excl',
            'field' => 'grand_total_excl',
            'value' => $source->getGrandTotal()-$source->getTaxAmount(),
            'label' => __('Grand Total (Excl. GST)'),
        ]), 'last');

        $this->_totals['grand_total']->setData('label', __('Grand Total (Inc. GST)'));

        return $result;
    }
}
