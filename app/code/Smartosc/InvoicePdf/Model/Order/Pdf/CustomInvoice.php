<?php

namespace Smartosc\InvoicePdf\Model\Order\Pdf;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Smartosc\CustomBundleProduct\Helper\CartItemHelper;
use Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService;

/**
 * Class CustomInvoice
 * @package Smartosc\InvoicePdf\Model\Order\Pdf
 */
class CustomInvoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    use PdfPrintTrait;

    const GRAND_TOTAL_INCL_TAX = 'Grand Total (Incl. GST)';
    const GRAND_TOTAL_EXCL_TAX = 'Grand Total (Excl. Tax)';

    /**
     * @var Database
     */
    private $fileStorageDatabase;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $_resource;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var array
     */
    protected $feedOfHeader = [
        'ProductName' => 35,
        'ProductSku' => 380,
        'Qty' => 440,
        'Price' => 500
    ];


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
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * CustomInvoice constructor.
     * @param Data $paymentData
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param Config $pdfConfig
     * @param CollectionFactory $productCollectionFactory
     * @param TaxCalculationInterface $taxCalculation
     * @param Factory $pdfTotalFactory
     * @param ItemsFactory $pdfItemsFactory
     * @param TimezoneInterface $localeDate
     * @param StateInterface $inlineTranslation
     * @param Renderer $addressRenderer
     * @param StoreManagerInterface $storeManager
     * @param ResolverInterface $localeResolver
     * @param ResourceConnection $resource
     * @param FilterManager $filterManager
     * @param \Smartosc\Checkout\Helper\Order\Data $helper
     * @param CartItemHelper $addonHelper
     * @param ReportService $reportService
     * @param array $data
     * @param Database|null $fileStorageDatabase
     */
    public function __construct(
        \Magento\Payment\Helper\Data                                    $paymentData,
        \Magento\Framework\Stdlib\StringUtils                           $string,
        \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig,
        \Magento\Framework\Filesystem                                   $filesystem,
        Config                                                          $pdfConfig,
        \Magento\Tax\Api\TaxCalculationInterface                        $taxCalculation,
        \Magento\Sales\Model\Order\Pdf\Total\Factory                    $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory                     $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface            $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface              $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer                     $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface                      $storeManager,
        \Magento\Framework\Locale\ResolverInterface                     $localeResolver,
        \Magento\Framework\App\ResourceConnection                       $resource,
        \Magento\Framework\Filter\FilterManager                         $filterManager,
        \Smartosc\Checkout\Helper\Order\Data                            $helper,
        \Smartosc\CustomBundleProduct\Helper\CartItemHelper             $addonHelper,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService,
        array                                                           $data = [],
        Database                                                        $fileStorageDatabase = null
    )
    {
        $this->reportService = $reportService;
        $this->_resource = $resource;
        $this->helper = $helper;
        $this->filterManager = $filterManager;
        $this->addonHelper = $addonHelper;
        $this->taxCalculation = $taxCalculation;
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(Database::class);
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $localeResolver, $data);
    }


    /**
     * @param string $shippingMethodCode
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getShippingMethodTitle($shippingMethodCode)
    {

        $connection = $this->_resource->getConnection();
        $query = $connection->quoteInto("SELECT DISTINCT(method_title) FROM `quote_shipping_rate` where code = ?", $shippingMethodCode);

        $rs = $connection->query($query);
        $rs = $rs->fetchObject();

        return $rs ? $rs->method_title : '';
    }


    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Product'), 'feed' => $this->feedOfHeader['ProductName']];

        $lines[0][] = ['text' => __('Item Code'), 'feed' => $this->feedOfHeader['ProductSku'], 'align' => 'right'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => $this->feedOfHeader['Qty'], 'align' => 'right'];

        $lines[0][] = ['text' => __('Price'), 'feed' => $this->feedOfHeader['Price'], 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->_localeResolver->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();

            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            $index = 0;

            $flag = [];
            $addonIds = [];
            $addonsByBundle = [];
            foreach ($invoice->getAllItems() as $_item) {
                if ($_item->getOrderItem()->getProductType() == 'bundle') {
                    $addons = $this->addonHelper->getAddonList($_item->getOrderItem());
                    $addonsByBundle[$_item->getOrderItemId()] = $addons;
                    $addonIds = array_merge($addonIds, $addons);
                }
            }
            foreach ($invoice->getAllItems() as $item) {
                /* Draw item */
                if ($item->getOrderItem()->getProductType() !== 'bundle') {
                    /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
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
                    if ($item->getOrderItem()->getParentItem()) {
                        if ($item->getOrderItem()->getParentItem()->getProductType() !== 'bundle') {
                            if (in_array($item->getProductId(), $addonIds)) {
                                $this->_drawItem($item, $page, $order);
                                $index++;
                                $page = end($pdf->pages);
                            }
                        }
                    } else {
                        if (!in_array($item->getProductId(), $addonIds)) {
                            $this->_drawItem($item, $page, $order);
                            $index++;
                            $page = end($pdf->pages);
                        }
                    }
                } else {
                    /** @var \Magento\Sales\Model\Order\Item $orderItem */
                    $orderItem = $item->getOrderItem();
                    $childItems = [];
                    $find = false;
                    foreach ($invoice->getAllItems() as $_item) {
                        if ($_item->getOrderItem()->getParentItem()) {
                            if ($_item->getOrderItem()->getParentItemId() == $orderItem->getItemId()) {
                                if ($orderItem->getIsRepotNotRequire()) {
                                    $find = true;
                                }
                                array_push($childItems, $_item);
                            }
                        }
                    }
                    $bundleItemId = $item->getOrderItemId();
                    $addonList = $addonsByBundle[$bundleItemId] ?? [];
                    $count = 0;
                    if (!empty($addonList)) {
                        foreach ($invoice->getAllItems() as $_item) {
                            if (in_array($_item->getProductId(), $addonList)) {
                                if (empty($flag[$_item->getProductId()]) || (!$flag[$_item->getProductId()])) {
                                    $length = strlen($_item->getOrderItem()->getSku());
                                    if ($length > 17) {
                                        $count += ceil($length / 17);
                                    }
                                }
                            }
                        }
                    }
                    $numLine = count($childItems) + $count;
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
                    $fakeRepot = null;
                    foreach ($childItems as $childInvoiceItem) {
                        if (null === $fakeRepot) {
                            $fakeRepot = clone $childInvoiceItem;
                            $repot = $this->reportService->getProduct();
                            $fakeRepot->setName($repot->getName())
                                ->setSku($repot->getSku())
                                ->setQty(0)->setProductId($repot->getId())
                                ->setPrice($repot->getPrice())
                                ->setRowTotalInclTax(0);
                        }
                        $this->_drawItem($childInvoiceItem, $page, $order);
                    }
                    if ($find) {
                        $this->_drawItem($fakeRepot, $page, $order);
                    }
                    if (!empty($addonList)) {
                        foreach ($invoice->getAllItems() as $_item) {
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
            }
            /* Add totals */
            $page = $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->insertTextLeft($page);
        $this->insertTextRight($page);
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Calculate number of lines, which an order item consume
     *
     * @param \Magento\Sales\Model\Order\Invoice\Item $item
     * @return int
     */
    protected function getNumLineItem($item)
    {

        $result = 0;

        $productName = html_entity_decode($item->getName());
        $productNameArr = $this->string->split($productName, 64, true, true);
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
     * Retrieve item options
     *
     * @return array
     */
    public function getItemOptions($item)
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
     * @param \Zend_Pdf_Page $page
     * @param string $text
     * @throws \Zend_Pdf_Exception
     */
    public function insertDocumentNumber(\Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 450, $docHeader[1] - 15, 'UTF-8');
    }

    /**
     * Insert logo to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Pdf_Exception
     */
    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/sales/store/logo/' . $image;
            if ($this->fileStorageDatabase->checkDbUsage() &&
                !$this->_mediaDirectory->isFile($imagePath)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($imagePath);
            }
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $top = 830;
                //top border of the page
                $widthLimit = 270;
                //half of the page width
                $heightLimit = 270;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @throws \Zend_Pdf_Exception
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
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 450, $top -= 30, 'UTF-8');
            $top += 15;
        }

        $top -= 30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->scopeDate(
                $order->getStore(),
                $order->getCreatedAt(),
                true
            )->format('d-M-Y'),
            450,
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


        //$this->_printBuildingAndFloor($order, $billingAddress);

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

            //$this->_printShippingBuildingAndFloor($order, $shippingAddress);


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
        $page->drawRectangle(25, $top - 25, 570, $top - 47 - $addressesHeight);
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
            $this->y = $addressesStartY;
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


            $this->y -= 10;
            /*$totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($order->getShippingInclTax())
                . ")";*/

            $shippingType = $order->getShippingType();
            if ($shippingType === 'delivery') {
                $this->y += 10;
                /** @var string $deliveryDate */
                if ($deliveryDate = $order->getDeliveryDate()) {
                    $deliveryDate = str_replace("--", "-", $this->helper->getDate($deliveryDate));
                    $page->drawText(
                        __('Delivery Date: %1', $deliveryDate),
                        285,
                        $this->y,
                        'UTF-8'
                    );
                }

                if ($order->getDeliveryNote()) {
                    $this->y -= 15;
                    $this->_drawTextMultiLine($page, $order->getDeliveryNote(), 'Delivery Note', 285);
                }

                if ($order->getAcceptAuthorize()) {
                    $storeId = $this->_storeManager->getStore()->getId();
                    $authorizeMessage = $this->_scopeConfig->getValue('smartosc_authorize/authorize_settings/authorize_message', ScopeInterface::SCOPE_STORE, $storeId);
                    $this->y -= 15;
                    $this->_drawTextMultiLine($page, $authorizeMessage, 'Authorize Message', 285);
                }

                $orderDescription = $order->getShippingDescription();
                if ($orderDescription) {
                    $page->drawText($orderDescription, 285, $this->y - $topMargin, 'UTF-8');
                }

                $this->y -= 15;
                $totalShippingChargesText = "("
                    . __('Total Shipping Charges')
                    . " "
                    . $order->formatPriceTxt($order->getShippingInclTax())
                    . ")";

                $page->drawText($totalShippingChargesText, 285, $this->y - $topMargin, 'UTF-8');
                $this->y -= 15;
            } elseif ($shippingType === 'in_store_pickup') {

                $this->y += 25;

                if ($order->getPickupTime()) {

                    $page->drawText(__("Store pickup Time: %1", $order->getPickupTime()), 285, $this->y - $topMargin, 'UTF-8');

                }

                if ($pickupDate = $order->getPickupDate()) {
                    $pickupDate = $this->helper->getDate($pickupDate);
                    $this->y -= 15;
                    $page->drawText(__("Store pickup Date: %1", $pickupDate), 285, $this->y - $topMargin, 'UTF-8');

                }

                if ($order->getPickupComments()) {
                    $this->y -= 30;
                    $this->_drawTextMultiLine($page, $order->getPickupComments(), 'Store pickup note', 285);

                }
            } else {
                $this->y += 35;
            }

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
                }
            }

            $yShipments = $this->y;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
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
     * {@inheritdoc}
     */
    protected function _getTotalsList($order = null)
    {
        $totals = $this->_pdfConfig->getTotals();
        $taxPercent = (float)$order->getAllVisibleItems()[0]->getData()['tax_percent'];

        if (isset($totals['grand_total']) && isset($totals['tax'])) {
            $totalExclTax = [
                'title' => self::GRAND_TOTAL_INCL_TAX,
                'sort_order' => 1000,
                'font_size' => 7,
                'display_zero' => true,
                'model' => 'Smartosc\InvoicePdf\Model\Order\Pdf\Total\GrandTotalExclTax'
            ];

            $totals[] = $totalExclTax;
        }

        $this->y -= 15;
        $map = [
            'subtotal' => ['sortOrder' => 10, 'title' => 'Subtotal'],
            'discount' => ['sortOrder' => 20, 'title' => false],
            'shipping' => ['sortOrder' => 30, 'title' => false],
            'grand_total' => ['sortOrder' => 100, 'title' => self::GRAND_TOTAL_INCL_TAX],
            'tax' => ['sortOrder' => 120, 'title' => __('GST (%1%)', $taxPercent)]
        ];


        foreach ($map as $key => $newInfo) {
            if (array_key_exists($key, $totals)) {
                $totals[$key]['sort_order'] = $newInfo['sortOrder'];
                if ($newInfo['title']) {
                    $totals[$key]['title'] = $newInfo['title'];
                }
            }
        }

        usort($totals, [$this, '_sortTotalsList']);
        $totalModels = [];
        foreach ($totals as $totalInfo) {
            $class = empty($totalInfo['model']) ? null : $totalInfo['model'];
            $totalModel = $this->_pdfTotalFactory->create($class);
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\AbstractModel $source
     * @return \Zend_Pdf_Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function insertTotals($page, $source)
    {
        $order = $source->getOrder();
        $totals = $this->_getTotalsList($order);
        $taxPercent = (float)$order->getAllVisibleItems()[0]->getData()['tax_percent'];
        $lineBlock = ['lines' => [], 'height' => 15];
        $greyBlock = ['lines' => [], 'height' => 15];
        foreach ($totals as $total) {
            $total->setOrder($order)->setSource($source);

            if ($total->canDisplay()) {
                $total->setFontSize(10);
                if ($total instanceof \Magento\Tax\Model\Sales\Pdf\Shipping) {
                    $shipping_method = $order->getData('shipping_method');
                    $shippingTitle = $this->getShippingMethodTitle($shipping_method);
                    foreach ($total->getTotalsForDisplay() as $totalData) {

                        $lineBlock['lines'][] = [
                            [
                                'text' => $shippingTitle . ':',
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => $totalData['amount'],
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];
                    }

                } else {
                    foreach ($total->getTotalsForDisplay() as $totalData) {
                        if (str_contains($totalData['label'], self::GRAND_TOTAL_EXCL_TAX) || str_contains($totalData['label'], __('GST (%1%)', $taxPercent))) {
                            $greyBlock['lines'][] = [
                                [
                                    'text' => $totalData['label'],
                                    'feed' => 475,
                                    'align' => 'right',
                                    'font_size' => $totalData['font_size']
                                ],
                                [
                                    'text' => $totalData['amount'],
                                    'feed' => 565,
                                    'align' => 'right',
                                    'font_size' => $totalData['font_size']
                                ],
                            ];
                        } else {
                            $lineBlock['lines'][] = [
                                [
                                    'text' => $totalData['label'],
                                    'feed' => 475,
                                    'align' => 'right',
                                    'font_size' => $totalData['font_size'],
                                    'font' => 'bold',
                                ],
                                [
                                    'text' => $totalData['amount'],
                                    'feed' => 565,
                                    'align' => 'right',
                                    'font_size' => $totalData['font_size'],
                                    'font' => 'bold'
                                ],
                            ];
                        }
                    }
                }
            }
        }

        $this->y -= 20;
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.34, 0.31, 0.25));
        $page = $this->drawLineBlocks($page, [$greyBlock]);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $this->_setFontBold($page, 10);
        return $page;
    }

    /**
     * @param $page
     */
    public function insertTextLeft($page)
    {
        $page->drawLine(25, $this->y, 570, $this->y);
        $this->y -= 25;
        $page->drawText(__('Far East Flora Pte Ltd'), 35, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText(__('555 Thomson Road Singapore 298140'), 35, $this->y, 'UTF-8');
        $this->y -= 15;
        //$page->drawText(__('Tel: +65 6254 6662'), 35, $this->y, 'UTF-8');
    }

    /**
     * @param $page
     */
    public function insertTextRight($page)
    {

        $this->y += 30;
        $page->drawText(__('GST Reg. No: M2-0041222-2'), 450, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText(__('UEN No.: 198004108E'), 450, $this->y, 'UTF-8');
    }

    /**
     * Set font as regular
     * @param \Zend_Pdf_Page $object
     * @param int $size
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
     * @param \Zend_Pdf_Page $object
     * @param int $size
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
     * @param \Zend_Pdf_Page $object
     * @param int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }
}
