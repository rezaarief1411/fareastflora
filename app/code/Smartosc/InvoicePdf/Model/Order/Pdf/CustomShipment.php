<?php

namespace Smartosc\InvoicePdf\Model\Order\Pdf;

use Magento\Store\Model\ScopeInterface;

/**
 * Class CustomShipment
 * @package Smartosc\InvoicePdf\Model\Order\Pdf
 */
class CustomShipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    use PdfPrintTrait;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\Shipment\CustomShipmentItem
     */
    protected $customShipmentItem;

    /**
     * @var \Smartosc\Checkout\Helper\Order\Data
     */
    protected $helper;

    /**
     * @var \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper
     */
    protected $addonHelper;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $reportService;
    /**
     * {@inheritDoc}
     * @see \Magento\Sales\Model\Order\Pdf\Shipment::__construct()
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Smartosc\CustomBundleProduct\Model\Shipment\CustomShipmentItem $customShipmentItem,
        \Smartosc\Checkout\Helper\Order\Data $helper,
        \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService,
        array $data = []
    ) {
        $this->reportService = $reportService;
        $this->customShipmentItem = $customShipmentItem;
        $this->helper = $helper;
        $this->filterManager = $filterManager;
        $this->addonHelper = $addonHelper;
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $localeResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                $this->_localeResolver->emulate($shipment->getStoreId());
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }
            $page = $this->newPage();
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Packing Slip # ') . $shipment->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            $index = 0;

            $flag = [];
            $addonIds = [];
            $addonsByBundle = [];
            foreach ($shipment->getAllItems() as $_item){
                if ($_item->getOrderItem()->getProductType() == 'bundle') {
                    $addons = $this->addonHelper->getAddonList($_item->getOrderItem());
                    $addonsByBundle[$_item->getOrderItemId()] = $addons;
                    $addonIds = array_merge($addonIds, $addons);
                }
            }
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */

                if ($item->getOrderItem()->getProductType()!=='bundle') {
                    if (!in_array($item->getProductId(),$addonIds)) {
                        $numLine = $this->getNumLineItem($item);
                        $height = 20 * $numLine;

                        if ($this->y - $height < 15) {
                            $page = $this->newPage(['table_header' => true]);
                            $index = 0;
                        }

                        if ($index % 2 == 1) {
                            $this->_setFontRegular($page, 10);
                            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
                            $page->setLineWidth(0.5);
                            $top = $this->y + 10;
                            if ($this->y - $height < 15) {
                                $page = $this->newPage(['table_header' => true]);
                            }

                            $page->drawRectangle(25, $top, 570, $top - $height);
                            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
                        }
                        $this->_drawItem($item, $page, $order);

                        $index++;
                    }
                } else {
                    /** @var \Magento\Sales\Model\Order\Item $orderItem */
                    $orderItem = $item->getOrderItem();
                    $childItems = $this->customShipmentItem
                        ->setItemId($orderItem->getItemId())
                        ->getChildShipmentItems();

                    $bundleItemId = $item->getOrderItemId();
                    $addonList = $addonsByBundle[$bundleItemId] ?? [];
                    $count = 0;
                    if (!empty($addonList)) {
                        foreach ($shipment->getAllItems() as $_item) {
                            if (in_array($_item->getProductId(), $addonList)) {
                                if (empty($flag[$_item->getProductId()]) || (!$flag[$_item->getProductId()])) {
                                    $length = strlen($_item->getOrderItem()->getSku());
                                    if ($length > 25) {
                                        $count += ceil($length / 25);
                                    }
                                }
                            }
                        }
                    }
                    $numLine = count($childItems) + $count;
                    $height = 20 * $numLine;

                    if ($this->y - $height  < 15) {
                        $page = $this->newPage(['table_header' => true]);
                        $index = 0;
                    }

                    if ($index % 2 == 1) {
                        $this->_setFontRegular($page, 10);
                        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
                        $page->setLineWidth(0.5);
                        $top = $this->y + 10;
                        if ($this->y - $height  < 15) {
                            $page = $this->newPage(['table_header' => true]);
                        }

                        $page->drawRectangle(25, $top, 570, $top - $height);
                        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
                    }
                    $find = false;
                    $fakeRepot = null;
                    foreach ($childItems as $childShipmentItem) {
                        $this->_drawItem($childShipmentItem, $page, $order);
                    }
                    if ($orderItem->getIsRepotNotRequire()) {
                        $fakeRepot = clone $childShipmentItem;
                        $fakeRepot->setProductId($this->reportService->getProductId());
                        $fakeRepot->setName($this->reportService->getName());
                        $fakeRepot->setSku($this->reportService->getItemCode());
                        $fakeRepot->setQty(0);
                        $this->_drawItem($fakeRepot, $page, $order);
                    }
                     if (!empty($addonList)) {
                         foreach ($shipment->getAllItems() as $_item) {
                             if (in_array($_item->getProductId(), $addonList)) {
                                 if (empty($flag[$_item->getProductId()]) || (!$flag[$_item->getProductId()])) {
                                     $this->_drawItem($_item, $page, $order);
                                     $flag[$_item->getProductId()] = true;
                                 }
                             }
                         }
                     }
                    $index++;
                }
                $page = end($pdf->pages);
            }
            if ($shipment->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_drawSignature($page);

        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * @param $item
     * @return array
     */
    protected function getItemOptions($item)
    {
        $result = [];
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }

    /**
     * Calculate number of lines, which an order item consume
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @return int
     */
    protected function getNumLineItem($item)
    {

        $result = 0;

        $productName        = html_entity_decode($item->getName());
        $productNameArr     = $this->string->split($productName, 64, true, true);
        $numLineProductName = count($productNameArr);

        $result += $numLineProductName;

        // Product Options
        $productOptions = $this->getItemOptions($item);

        if ($productOptions) {
            foreach ($productOptions as $option) {
                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                }

                $optionDescription = sprintf('%s: %s', $option['label'], $printValue);

                $array = $this->string->split($optionDescription, 64, true, true);
                $result += count($array);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
            $top +=15;
        }

        $top -=30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->scopeDate(
                $order->getStore(),
                $order->getCreatedAt(),
                true
            )->format('d-M-Y'),
            35,
            $top,
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Billing Info:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            if ($order->getData('shipping_type') == 'in_store_pickup') {
                $page->drawText(__('Collection At:'), 285, $top - 15, 'UTF-8');
            } else {
                $page->drawText(__('Delivery Info:'), 285, $top - 15, 'UTF-8');
            }
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 50 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );

            $this->y = $addressesStartY;
            $this->_printShippingBuildingAndFloor($order, $shippingAddress);

            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Delivery Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }


        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($order->getShippingAmount())
                . ")";

            //$page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $noteHeight = 0;

            if ($order->getShippingType() == 'delivery') {
                $deliveryNote = $order->getDeliveryNote();
                // print delivery date and delivery note
                $this->_printDeliveryDetail($page, $order);
                // calculate height of delivery detail
                $noteHeight = $this->_getLongTextHeight($deliveryNote, 'Delivery Note');
            } elseif ($order->getShippingType() == 'in_store_pickup') {
                $pickupNote = $order->getPickupComments();
                // print delivery date and delivery note
                $this->_printPickupDetail($page, $order);
                // calculate height of delivery detail
                $noteHeight = $this->_getLongTextHeight($pickupNote, 'Store Pickup Note');
            }

            $giftMessageHeight = 0;
            $giftMessageFrom = $order->getGiftMessageFrom();
            $giftMessageTo = $order->getGiftMessageTo();
            $giftMessage = $order->getGiftMessage();

            if ($giftMessageFrom || $giftMessageTo || $giftMessage) {
                $addressesEndY = min($addressesEndY, $this->y);
                $this->y = $addressesEndY;
                $this->y -= 30;
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(25, $this->y, 570, $this->y - 25);

                $this->y -= 15;
                $this->_setFontBold($page, 12);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $page->drawText(__('Gift Message:'), 35, $this->y, 'UTF-8');

                $this->y -= 25;
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $gift = [
                    'from' => $giftMessageFrom,
                    'to' => $giftMessageTo,
                    'message' => $giftMessage
                ];
                foreach ($gift as $name => $value) {
                    $title = ucfirst($name);
                    $this->_drawTextMultiLine($page, $value, $title, 35);
                    $giftMessageHeight += $this->_getLongTextHeight($value, '');
                }
            }

            $noteHeight+=$giftMessageHeight + 140;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
//                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);
            $currentY -= $noteHeight;

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;

        }
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @throws \Zend_Pdf_Exception
     */
    private function _drawSignature($page)
    {
        $this->y -= 100;
        $page->drawText('Date:                      Time:', 25, $this->y);

        $this->_setFontRegular($page, 10);

        $page->setLineWidth(0.5);
        $this->y -= 4;
        $page->drawLine(25, $this->y, 210, $this->y);
        $this->y -= 15;
        $page->drawText('CUSTOMER\'S SIGNATURE & CO. STAMP', 25, $this->y);
        $this->y -= 15;
        ;
        $page->drawText('Received in good order & condition', 40, $this->y);
    }

    /**
     * @param $page
     * @param $order
     * @return void
     */
    private function _printDeliveryDetail($page, $order)
    {
        $date = str_replace("--", "-", $this->helper->getDate($order->getDeliveryDate()));
        $deliveryNote = $order->getDeliveryNote();
        $acceptAuthorize = $order->getAcceptAuthorize();

        // print date
        if ($date) {
            $this->y -= 15;
            $page->drawText('Delivery Date: ' . $date, 285, $this->y);
        }

        if ($deliveryNote) {
            $this->y -= 15;
            // print note
            $prefix = 'Delivery Note';
            $this->_drawTextMultiLine($page, $deliveryNote, $prefix, 285);
        }

        if ($acceptAuthorize) {
            $this->y -= 15;
            $prefix = 'Authorize Message';
            $storeId = $this->_storeManager->getStore()->getId();
            $authorizeMessage = $this->_scopeConfig->getValue('smartosc_authorize/authorize_settings/authorize_message', ScopeInterface::SCOPE_STORE, $storeId);
            $this->_drawTextMultiLine($page, $authorizeMessage, $prefix, 285);
        }
    }

    /**
     * @param $page
     * @param $order
     * @return void
     */
    private function _printPickupDetail($page, $order)
    {
        $time = $order->getPickupTime() ?: '';
        $date = $order->getPickupDate() ?: '';
        $note = $order->getPickupComments() ?: '';

        // print time
        if ($time) {
            $this->y -= 15;
            $page->drawText('Pickup Time: ' . $time, 285, $this->y);
        }

        // print date
        if ($date) {
            $date = $this->helper->getDate($date);
            $this->y -= 15;
            $page->drawText('Pickup Date: ' . $date, 285, $this->y);
        }

        if ($note) {
            $this->y -= 15;
            // print note
            $prefix = 'Pickup Note';
            $this->_drawTextMultiLine($page, $note, $prefix, 285);
        }
    }

    /**
     * Calculate height of a text block
     *
     * @param string $info
     * @param string $prefix
     * @return int
     */
    private function _getLongTextHeight($info, $prefix)
    {
        $notes = $this->_formatAddress(sprintf("%s: %s", $prefix, $info));
        $height = $this->_calcAddressHeight($notes);

        return $height;
    }

	/**
	 * Set font as regular
	 * @param  \Zend_Pdf_Page $object
	 * @param  int $size
	 * @return \Zend_Pdf_Resource_Font
	 */
	protected function _setFontRegular($object, $size = 7)
	{
		$font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
		$object->setFont($font, $size);
		return $font;
	}

	/**
	 * Set font as bold
	 * @param  \Zend_Pdf_Page $object
	 * @param  int $size
	 * @return \Zend_Pdf_Resource_Font
	 */
	protected function _setFontBold($object, $size = 7)
	{
		$font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES_BOLD);
		$object->setFont($font, $size);
		return $font;
	}

	/**
	 * Set font as italic
	 * @param  \Zend_Pdf_Page $object
	 * @param  int $size
	 * @return \Zend_Pdf_Resource_Font
	 */
	protected function _setFontItalic($object, $size = 7)
	{
		$font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES_ITALIC);
		$object->setFont($font, $size);
		return $font;
	}
}
