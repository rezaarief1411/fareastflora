<?php

namespace Smartosc\Checkout\Helper\Product\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

/**
 * Class Data
 * @package Smartosc\Checkout\Helper\Product\Price
 */
class Data
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Data constructor.
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @var float
     */
    protected $regularPrice;

    /**
     * @var float
     */
    protected $basePrice;

    /**
     * @param string $sku
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegularPriceBySku($sku)
    {
        $product = $this->productRepository->get($sku);

        return $this->getRegularPrice($product);
    }
    /**
     * @param Product $product
     * @return float
     */
    public function getRegularPrice($product)
    {

        if ($product->getTypeId() == 'configurable') {
            $this->basePrice = $product->getPriceInfo()->getPrice('regular_price');
            $this->regularPrice = $this->basePrice->getMinRegularAmount()->getValue();
        } elseif ($product->getTypeId() == 'bundle') {
            $this->regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
        } elseif ($product->getTypeId() == 'grouped') {
            $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($usedProds as $child) {
                if ($child->getId() != $product->getId()) {
                    $this->specialPrice += $child->getFinalPrice();
                }
            }
        } else {
            $this->regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
        }

        return $this->regularPrice;
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getSpecialPrice($product)
    {
        $specialPrice = $product->getSpecialPrice();

        $specialPriceFromDate = $product->getData('special_from_date');
        $specialPriceToDate = $product->getData('special_to_date');
        $today = time();

        if ($specialPrice && ($product->getData('price') > $product->getData('final_price'))) {
            if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) ||
                $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                return $specialPrice;
            }
        }

        return 0;
    }
}
