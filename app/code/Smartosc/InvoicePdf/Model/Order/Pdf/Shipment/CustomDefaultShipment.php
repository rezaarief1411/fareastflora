<?php

namespace  Smartosc\InvoicePdf\Model\Order\Pdf\Shipment;

use Magento\Sales\Model\Order\Pdf\Items\Shipment\DefaultShipment;

/**
 * Class CustomDefaultShipment
 */
class CustomDefaultShipment extends DefaultShipment
{
    /**
     * {@inheritdoc}
     */
    public function draw()
    {
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];
        
        // draw Product name
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($item->getName()), 64, true, true),
                'feed' => 100
            ]
        ];
        
        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 35];
        
        // draw SKU
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getSku($item)), 25),
            'feed' => 565,
            'align' => 'right',
        ];
        
        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                
                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                }
                
                $optionDescription = sprintf('%s: %s', $option['label'], $printValue);
                
                $lines[][] = [
                    'text' => $this->string->split($optionDescription, 64, true, true),
                    'font' => 'italic',
                    'feed' => 115,
                ];
            }
        }
        
        $lineBlock = ['lines' => $lines, 'height' => 20];
        
        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
