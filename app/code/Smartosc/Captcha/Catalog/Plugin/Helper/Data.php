<?php

namespace Smartosc\Catalog\Plugin\Helper;

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
		if (((!(empty($newToDate) && empty($newFromDate)) && ($newFromDate < $now || empty($newFromDate)) && ($newToDate > $now || empty($newToDate)) && ($newLabel != '')) || ((empty($newToDate) && ($newFromDate < $now)) && ($newLabel != ''))) && $product->getIsSalable()) {
			$html .= '<div class="product-label new-label"><div class="news-label">' . $newLabel . '</div></div>';
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

