<?php

namespace Smartosc\CustomBundleProduct\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer as CoreDefaultRenderer;
use Smartosc\CustomBundleProduct\Helper\Data as Helper;

/**
 * Class DefaultRenderer
 *
 * @package Smartosc\CustomBundleProduct\Block\Adminhtml\Order\View\Items\Renderer
 */
class DefaultRenderer extends CoreDefaultRenderer
{
    /**
     * @var \Smartosc\CustomBundleProduct\Helper\Data
     */
    protected $helper;

    /**
     * DefaultRenderer constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                   $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface      $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry                               $registry
     * @param \Magento\GiftMessage\Helper\Message                       $messageHelper
     * @param \Magento\Checkout\Helper\Data                             $checkoutHelper
     * @param \Smartosc\CustomBundleProduct\Helper\Data                 $helper
     * @param array                                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $messageHelper,
            $checkoutHelper,
            $data
        );
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @param string                        $column
     * @param null                          $field
     *
     * @return string
     */
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $qtyOrdered = (int)$item->getQtyOrdered();
        $isReportServiceProduct = $this->helper->isReportServiceProduct($item->getProductId()) && $qtyOrdered < 1;
        $html = '';
        switch ($column) {
            case 'product':
                if ($this->canDisplayContainer()) {
                    $html .= '<div id="' . $this->getHtmlId() . '">';
                }
                $html .= $this->getColumnHtml($item, 'name');
                if ($this->canDisplayContainer()) {
                    $html .= '</div>';
                }
                break;
            case 'status':
                $html = $isReportServiceProduct ? '' : $item->getStatus();
                break;
            case 'price-original':
                $html = $isReportServiceProduct ? '' : $this->displayPriceAttribute('original_price');
                break;
            case 'tax-amount':
                $html = $isReportServiceProduct ? '' : $this->displayPriceAttribute('tax_amount');
                break;
            case 'tax-percent':
                $html = $isReportServiceProduct ? '' : $this->displayTaxPercent($item);
                break;
            case 'discont':
                $html = $isReportServiceProduct ? '' : $this->displayPriceAttribute('discount_amount');
                break;
            case 'qty':
            case 'subtotal':
            case 'price':
                $html = $isReportServiceProduct ? '' : parent::getColumnHtml($item, $column, $field);
                break;
            case 'total':
                $html = $isReportServiceProduct ? 'Not Require' : parent::getColumnHtml($item, $column, $field);
                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }
}
