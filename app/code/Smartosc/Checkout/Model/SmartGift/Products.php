<?php

namespace Smartosc\Checkout\Model\SmartGift;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Products
 * @package Smartosc\Checkout\Model\SmartGift
 */
class Products
{

    const NODE_CATEGORY_GIFT = 'mpanel/category_gift/gift_category';

    /**
     * @var \Smartosc\Checkout\Helper\Data
     */
    protected $helper;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Smartosc\Checkout\Helper\Product\Data
     */
    protected $productDataHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Smartosc\Checkout\Helper\Product\Price\Data
     */
    protected $productPriceData;

    /**
     * Products constructor.
     * @param \Smartosc\Checkout\Helper\Data $helper
     * @param \Smartosc\Checkout\Helper\Product\Data $productDataHelper
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Smartosc\Checkout\Helper\Data $helper,
        \Smartosc\Checkout\Helper\Product\Data $productDataHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Smartosc\Checkout\Helper\Product\Price\Data $productPriceData
    ) {
        $this->productPriceData = $productPriceData;
        $this->priceHelper = $priceHelper;
        $this->productDataHelper = $productDataHelper;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $catId = $this->helper->getStoreConfig(self::NODE_CATEGORY_GIFT); // category of gift
        $category = $this->helper->getCategoryModel()->load($catId);
        $products = $category->getProductsPosition();
        $productIds = array_keys($products);
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in')->create();
        $productsAsGift = $this->productRepository->getList($searchCriteria)->getItems();
        $gifts = [];

        foreach ($productsAsGift as $item) {
            $product  = $this->productRepository->getById($item->getId());
            $imageUrl = $this->productDataHelper->getProductImageUrl($item->getId());
            $regularPrice = $this->productPriceData->getRegularPrice($product);
            $specialPrice = $this->productPriceData->getSpecialPrice($product);

            $gifts[] = [
                'id' => $item->getId(),
                'img_url' => $imageUrl,
                'title' => $item->getName(),
                'sku' => $item->getSku(),
                'price' => $this->formatPrice($regularPrice),
                'specialPrice' => $this->formatPrice($specialPrice)
            ];
        }

        return $gifts;
    }

    /**
     * @param float $amount
     * @return string
     */
    protected function formatPrice($amount)
    {
        return  $this->priceHelper->currency($amount, true, false);
    }
}
