<?php

namespace Smartosc\InvoicePdf\Model\Order\Pdf\Total;

/**
 * Class GrandTotalExclTax
 * @package Smartosc\InvoicePdf\Model\Order\Pdf\Total
 */
class GrandTotalExclTax extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * {@inheritdoc}
     */
    public function getTotalsForDisplay()
    {

        $amountExclTax = $this->getAmount()['grand_total'] - $this->getSource()->getTaxAmount();
        $amountExclTax = $this->getOrder()->formatPriceTxt($amountExclTax);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        return [
            [
                'amount' => $this->getAmountPrefix() . $amountExclTax,
                'label' => __('Grand Total (Excl. Tax)') . ':',
                'font_size' => $fontSize,
            ],
        ];
    }
}
