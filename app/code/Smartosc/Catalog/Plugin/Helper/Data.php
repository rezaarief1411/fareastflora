<?php

namespace Smartosc\Catalog\Plugin\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use phpDocumentor\Reflection\Types\Null_;

/**
 * Class Data
 * @package Smartosc\Catalog\Plugin\Helper
 */
class Data
{
	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $date;

	/**
	 * Data constructor.
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
	 */
	public function __construct(\Magento\Framework\Stdlib\DateTime\DateTime $date)
	{
		$this->date = $date;
	}

	/**
	 * @param \MGS\Mpanel\Helper\Data $subject
	 * @param $result
	 * @param $product
	 * @return string|string[]
	 */

	public function aroundGetProductLabel(\MGS\Mpanel\Helper\Data $subject, callable $proceed, $product)
	{
		$html = '';
		$newLabel = $subject->getStoreConfig('mpanel/catalog/new_label');
		$saleLabel = $subject->getStoreConfig('mpanel/catalog/sale_label');

		// Sale label
		$price = 0;
		if ($product->getPriceInfo() && $product->getPriceInfo()->getPrice('regular_price')) {
			$regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
			if ($regularPrice) {
				$price = $regularPrice;
			}
		}
		$finalPrice = $product->getFinalPrice();
		if (($finalPrice < $price) && ($saleLabel != '') && $product->getIsSalable()) {
            $html .= '<div class="product-label sale-label"><div class="sale-labels">'. __('Sale') .'</div><div class="sale-percents">' . '-' . $saleLabel .'</div></div>';
		}

		// New label
		$now = $this->date->gmtDate();
		$dateTimeFormat = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
		$newFromDate = $product->getNewsFromDate();
		$newFromDate = date($dateTimeFormat, strtotime($newFromDate));
		$newToDate = $product->getNewsToDate();
		$newToDate = date($dateTimeFormat, strtotime($newToDate));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $repository = $objectManager->get(ProductRepositoryInterface::class);
        $products = $repository->get($product->getSku());
        $freeLabel =  '';
        $freeLabelColor = '';
        if(is_object($products->getCustomAttribute('free_label'))){
            $freeLabel = $products->getCustomAttribute('free_label')->getValue();
        }
        if(is_object($products->getCustomAttribute('free_label_color'))){
            $freeLabelColor = $products->getCustomAttribute('free_label_color')->getValue();
        }
		if (((!(empty($newToDate) && empty($newFromDate)) && ($newFromDate < $now || empty($newFromDate)) && ($newToDate > $now || empty($newToDate)) && ($newLabel != '')) || ((empty($newToDate) && ($newFromDate < $now)) && ($newLabel != ''))) && $product->getIsSalable()) {
            if (($finalPrice < $price) && ($saleLabel != '')) {
                if (($freeLabel != '') && ($freeLabelColor != '')) {
                    $html .= '<div class="product-label free-label"><div class="frees-label" style="background-color:' . $freeLabelColor . '">' . $freeLabel . '</div></div>';
                } else {
                    $html .= '<div class="product-label new-label"><div class="news-label">' . $newLabel . '</div></div>';
                }
            } else {
                $html .= '<div class="product-label free-label"><div class="frees-label" style="background-color:' . $freeLabelColor . '">' . $freeLabel . '</div></div>';
                $html .= '<div class="product-label new-label"><div class="news-label">' . $newLabel . '</div></div>';
            }
		} else {
            if (($freeLabel != '') && ($freeLabelColor != '') && $product->getIsSalable()) {
                $html .= '<div class="product-label free-label"><div class="frees-label" style="background-color:' . $freeLabelColor . '">' . $freeLabel . '</div></div>';
            }
            if ($product->getShowNewLabel()) {
            	$html .= '<div class="product-label new-label"><div class="news-label">' . $newLabel . '</div></div>';
            }
        }
		$percent = $this->getDiscountPercent($product);
		if ($percent) {
			$html = str_replace('{{PERCENT}}', $percent . "%", $html);
		}
		return $html;
	}

	/**
	 * @param $product
	 * @return float|int
	 */
	public function getDiscountPercent($product)
	{
		$pecent = 0;
		$regularPrice = 0;
		$finalPrice = 0;

		if ($product->getTypeId() == "simple") {
			$regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
			$finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
		}

		if ($product->getTypeId() == 'configurable') {
			$basePrice = $product->getPriceInfo()->getPrice('regular_price');
			$regularPrice = $basePrice->getMinRegularAmount()->getValue();
			$finalPrice = $product->getFinalPrice();
		}

		if ($product->getTypeId() == 'bundle') {
			$regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
			$finalPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
		}

		if ($product->getTypeId() == 'grouped') {
			$usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
			foreach ($usedProds as $child) {
				if ($child->getId() != $product->getId()) {
					$regularPrice += $child->getPrice();
					$finalPrice += $child->getFinalPrice();
				}
			}
		}

		if ($finalPrice < $regularPrice) {
			$pecent = round(($regularPrice - $finalPrice) / $regularPrice * 100);
		}
		return $pecent;
	}
}

