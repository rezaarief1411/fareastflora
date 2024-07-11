<?php

namespace Smartosc\CustomBundleProduct\ViewModel\Cart\Item\Bundle;

/**
 * Class Renderer
 * @package Smartosc\CustomBundleProduct\ViewModel\Cart\Item\Bundle
 */
class Renderer implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\Option
     */
    protected $bundleProduct;

    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $reportService;

    /**
     * Renderer constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\Option $bundleProduct,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService,
        \MGS\Mpanel\Helper\Data $helper
    ) {
        $this->productRepository = $productRepository;
        $this->bundleProduct = $bundleProduct;
        $this->reportService = $reportService;
    }

    /**
     * @param $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemCode($product)
    {
        return $product->getSku();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    public function getOptions($item)
    {
        $products  = [];

        $arrayProducts = $this->bundleProduct->getProductOption($item);
        foreach ($arrayProducts as $i) {
            foreach ($i as $product) {
                $products[] = $product;
            }
        }
        if ($item->getIsRepotNotRequire()) {
            $products[] = $this->reportService->getItemData();
        }
        return $products;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductAttributes($product)
    {
        $customAttributes = $product->getCustomAttributes();
        $results=[];

        foreach ($customAttributes as $attribute) {

            if (!in_array($attribute->getAttributeCode(), ['color', 'by_size_dia_length'])) {
                continue;
            }

            $attributeCode = $attribute->getAttributeCode();

            $attributeTitle = $product
                ->getResource()
                ->getAttribute($attributeCode)
                ->getFrontend()
                ->getValue($product);

            if ($attributeCode == 'by_size_dia_length') {
                $attributeLabel = __('Size') . ':';
            } elseif ($attributeCode == 'color') {
                $attributeLabel = __('Colour') . ':';
            } else {
                $attributeLabel = $product
                    ->getResource()
                    ->getAttribute($attributeCode)
                    ->getFrontend()
                    ->getLabel($product);
            }

            $results[$attributeCode] = [
                'label' => $attributeLabel,
                'title' => $attributeTitle
            ];
        }

        return $results;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
     * @param \Magento\Catalog\Model\Product $product
     */
    public function getChildQuoteItemId($quoteItem, $product)
    {
        $result = null;

        $childQuoteItems = $quoteItem->getChildren();
        foreach ($childQuoteItems as $item) {
            if ($item->getProductId() == $product->getId()) {
                $result = $item->getId();
                break;
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
     * @param \Magento\Catalog\Model\Product $product
     * @return bool|\Magento\Quote\Model\Quote\Item\AbstractItem
     */
    public function getChildQuoteItem($quoteItem, $product)
    {
        $childQuoteItems = $quoteItem->getChildren();
        foreach ($childQuoteItems as $item) {
            if ($item->getProductId() == $product->getId()) {
                return $item;
            }
        }

        return false;
    }
}
