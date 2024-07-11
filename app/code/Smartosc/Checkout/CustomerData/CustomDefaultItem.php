<?php

namespace Smartosc\Checkout\CustomerData;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;

/**
 * Default item
 */
class CustomDefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $reportService;

    /**
     * @var \Smartosc\CustomBundleProduct\Helper\CartItemHelper
     */
    protected $addonHelper;

    /**
     * DefaultItem constructor.
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Escaper|null $escaper
     * @param ItemResolverInterface|null $itemResolver
     * @param \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService,
        \Magento\Framework\Escaper $escaper = null,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver = null,
        \Smartosc\CustomBundleProduct\Helper\CartItemHelper $addonHelper
    ) {
        $this->reportService = $reportService;
        $this->escaper = $escaper?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        $this->itemResolver = $itemResolver?:
            ObjectManager::getInstance(\Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface::class);
        $this->addonHelper = $addonHelper;
        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper,
            $escaper,
            $itemResolver
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        $result = parent::doGetItemData();
        $product = $this->item->getProduct();
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $result['smart_options'] = $this->getSmartOptionList();
        } else {
            $result['is_addon'] = $this->_isAddon($this->item);
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getSmartOptionList()
    {
        $result = $childProductIds = [];
        foreach ($this->item->getChildren() as $item) {
            $result[] = $this->_resolveOptionData($item);
            $childProductIds[] = $item->getProduct()->getId();

        }
        // verify if report-service is on quote, add a fake item if report service is not on quote
        if ($this->item->getIsRepotNotRequire()) {
            $result[] = $this->reportService->getItemData()->getData();
        }
        return $result;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    private function _resolveOptionData($item)
    {
        $productName = $item->getName();
        $defaultItem = $this->item;
        $this->item = $item;
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $result = [
            'is_pseudo'=> false,
            'parent_item_id' => $item->getParentItemId() * 1,
            'parent_qty' => $defaultItem->getQty(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getProduct()->getFinalPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'canApplyMsrp' => false,
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'reportServiceProductId' => $this->reportService->isReportServiceProduct($this->item->getProduct()->getId())
        ];

        $this->item = $defaultItem;

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    private function _isAddon($item)
    {
        return $this->addonHelper->isAddonItemId($item->getItemId());
    }
}
