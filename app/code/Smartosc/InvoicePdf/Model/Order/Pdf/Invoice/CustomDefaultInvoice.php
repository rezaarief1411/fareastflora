<?php

namespace Smartosc\InvoicePdf\Model\Order\Pdf\Invoice;

use Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice;

/**
 * Class CustomDefaultInvoice
 * @package Smartosc\InvoicePdf\Model\Order\Pdf\Invoice
 */
class CustomDefaultInvoice extends \Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice
{
    /**
     * @var \Smartosc\CustomBundleProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $feedInfo = [
        'ProductName' => 35,
        'ProductSku' => 400,
        'Price' => 510,
        'Qty' => 440
    ];

    /**
     * CustomDefaultInvoice constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Smartosc\CustomBundleProduct\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Smartosc\CustomBundleProduct\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $taxData, $filesystem, $filterManager, $string, $resource, $resourceCollection, $data);
    }
    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        $qtyOrdered = $item->getQty();
        $isReportService = $this->helper->isReportServiceProduct($item->getProductId()) && $qtyOrdered < 1;

        // draw Product name
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($item->getName()), 64, true, true),
                'feed' => $this->feedInfo['ProductName']
            ]
        ];

        // draw SKU
        if (!$isReportService) {
            $lines[0][] = [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($this->getSku($item)), 17),
                'feed' => $this->feedInfo['ProductSku'],
                'align' => 'right',
            ];
        }

        // draw QTY
        if (!$isReportService) {
            $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => $this->feedInfo['Qty'], 'align' => 'right'];
        }

        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = $this->feedInfo['Price'];
        $feedSubtotal = $feedPrice + 55;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                $i++;
            }
            // draw Price
            if (!$isReportService) {
                $lines[$i][] = [
                    'text' => $priceData['price'],
                    'feed' => $feedPrice,
                    'font' => 'bold',
                    'align' => 'right',
                ];
                // draw Subtotal
                $lines[$i][] = [
                    'text' => $priceData['subtotal'],
                    'feed' => $feedSubtotal,
                    'font' => 'bold',
                    'align' => 'right',
                ];
            } else {
                $lines[$i][] = [
                    'text' => __("Not Require"),
                    'feed' => $feedSubtotal,
                    'font' => 'bold',
                    'align' => 'right',
                ];
            }
            $i++;
        }

        // custom options
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
                    'feed' => 35,
                ];

            }
        }


        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        $this->setPage($page);
    }
}
