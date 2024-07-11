<?php

namespace Smartosc\InvoicePdf\Model\Order\Pdf;

/**
 * Trait PdfPrintTrait
 * @package Smartosc\InvoicePdf\Model\Order\Pdf
 */
trait PdfPrintTrait
{
    /**
     * @param \Zend_Pdf_Page $page
     * @param string $body
     * @param int $x
     */
    protected function drawParagraph($page, $body, $x = 285)
    {
        $lines = $this->_formatAddress($body);

        foreach ($lines as $line) {
            if ($line !== '') {
                $text = [];
                foreach ($this->string->split($line, 45, true, true) as $_value) {
                    $text[] = $_value;
                }

                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), $x, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }
    }


    /**
     * @param \Zend_Pdf_Page $page
     * @param string $multiLineText
     * @param string $prefix
     * @param int $x
     * @return void
     */
    protected function _drawTextMultiLine($page, $multiLineText, $prefix, $x)
    {
        $notes = $this->_formatAddress(sprintf("%s: %s", $prefix, $multiLineText));

        if (strtolower($prefix) != 'message') {
            foreach ($notes as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), $x, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }
        } else {
            $text = [];
            $i = 0;

            $text[] = $prefix . ": " . substr($multiLineText, 0, 119);
            foreach ($this->string->split(substr($multiLineText, 119, strlen($multiLineText)), 130, true, true) as $value) {
                if ($value !== '') {
                    $text[] = $value;
                }
            }
            foreach ($text as $part) {
                $page->drawText(strip_tags(ltrim($part)), $x, $this->y, 'UTF-8');
                $this->y -= 15;
            }
        }
    }

    /**
     * @param $order
     * @param $billingAddress
     */
    protected function _printBuildingAndFloor($order, $billingAddress)
    {
        if ($order->getBillingFloor() != "") {
            $billingAddress[1] .= __(", %1,", $order->getBillingFloor());
        }

        if ($order->getBillingBuilding() != "") {
            $billingAddress[1] .= __(" %1", $order->getBillingBuilding());
        }
    }

    /**
     * @param $order
     * @param $shippingAddress
     */
    protected function _printShippingBuildingAndFloor($order, $shippingAddress)
    {

        if ($order->getShippingFloor() != "") {
            $shippingAddress[1] .= __(", %1,", $order->getShippingFloor());
        }

        if ($order->getShippingBuilding() != "") {
            $shippingAddress[1] .= __(" %1", $order->getShippingBuilding());
        }
    }

    /**
     * Return item Sku
     *
     * @param mixed $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getOrderItem()->getProductOptionByCode('simple_sku')) {
            return $item->getOrderItem()->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }

    protected function _getDrawItem($item)
    {
        $lines = [];
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($item->getName()), 64, true, true),

            ]
        ];
        // draw SKU
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getSku($item)), 17),

            'align' => 'right',
        ];
        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'align' => 'right'];
        // draw item Prices
        $i = 0;

        // custom options
        $options = $this->getItemOptions($item);
        if ($options) {
            foreach ($options as $option) {

                if (isset($option['option_id']) && $option['option_id'] === 93) {

                    $color = '';
                    // Checking whether option value is not null
                    if ($option['value'] !== null) {
                        if (isset($option['print_value'])) {
                            $printValue = $option['print_value'];
                        } else {
                            $printValue = $this->filterManager->stripTags($option['value']);
                        }
                        $values = explode(', ', $printValue);
                        foreach ($values as $value) {
                            $color = $value;
                        }
                    }

                    // draw options label
                    $option['label'] .= ': ' . $color;
                    $lines[][] = [
                        'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                        'font' => 'italic',
                        'feed' => 35,
                    ];


                } else {

                    $filterManager = \Magento\Framework\App\ObjectManager::getInstance()->create("Magento\Framework\Filter\FilterManager");

                    // draw options label
                    $lines[][] = [
                        'text' => $this->string->split($filterManager->stripTags($option['label']), 40, true, true),
                        'font' => 'italic',
                        'feed' => 35,
                    ];

                    // Checking whether option value is not null
                    if ($option['value'] !== null) {
                        if (isset($option['print_value'])) {
                            $printValue = $option['print_value'];
                        } else {
                            $printValue = $this->filterManager->stripTags($option['value']);
                        }
                        $values = explode(', ', $printValue);
                        foreach ($values as $value) {
                            $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                        }
                    }
                }
            }
        }


        $lineBlock = ['lines' => $lines, 'height' => 20];

        return $lineBlock;
    }

    public function drawBackground($page, $item)
    {
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $top = $this->y + 10;
        $draw = $this->_getDrawItem($item);
        $height = $this->customDrawLineBlocks($draw);
        $page->drawRectangle(25, $top, 570, $top - $height);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
    }

    public function customDrawLineBlocks(array $itemsProp)
    {
        $lines = $itemsProp['lines'];
        $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;
        if (empty($itemsProp['shift'])) {
            $shift = 0;
            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    if (!is_array($column['text'])) {
                        $column['text'] = [$column['text']];
                    }
                    $top = 0;
                    //
                    foreach ($column['text'] as $part) {
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $shift += $maxHeight;
            }
            $itemsProp['shift'] = $shift;

        }

        return $shift;
    }
}
