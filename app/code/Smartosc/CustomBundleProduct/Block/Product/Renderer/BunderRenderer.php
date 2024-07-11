<?php

namespace Smartosc\CustomBundleProduct\Block\Product\Renderer;

use \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio;
use \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox;

/**
 * BunderRenderer trait that contains the common logic for all bundle renderer block.
 *
 * Any class using this trait is required to implement \Smartosc\CustomBundleProduct\Block\Product\Renderer\BundleRendererInterface
 *
 * @see \Smartosc\CustomBundleProduct\Block\Product\Renderer\BundleRendererInterface
 */
trait BunderRenderer
{
    /**
     * @var array
     */
    protected $selectedOptions = [];

    /**
     * @var array
     */
    protected $optionsPosition = [];


    /**
     * @var array
     */
    protected $_optionViews = [
        'radio' => 'Smartosc_CustomBundleProduct::product/listing/product/view/option/radio.phtml',
        'checkbox' => 'Smartosc_CustomBundleProduct::product/listing/product/view/option/checkbox.phtml'
    ];

    /**
     * @var array
     */
    protected $_optionViewModels = [
        'radio' => Radio::class,
        'checkbox' => Checkbox::class
    ];

    /**
     * @return array
     */
    public function getSelectedOptions()
    {
        return $this->selectedOptions;
    }

    /**
     * @param array $selectedOptions
     */
    public function setSelectedOptions($selectedOptions)
    {
        $this->selectedOptions = $selectedOptions;
    }

    /**
     * @return array
     */
    public function getOptionsPosition()
    {
        return $this->optionsPosition;
    }

    /**
     * @param array $optionsPosition
     */
    public function setOptionsPosition($optionsPosition)
    {
        $this->optionsPosition = $optionsPosition;
    }

    /**
     * @return array
     */
    public function getOptionViews()
    {
        return $this->_optionViews;
    }

    /**
     * @param array $optionViews
     */
    public function setOptionViews($optionViews)
    {
        $this->_optionViews = $optionViews;
    }

    /**
     * @return array
     */
    public function getOptionViewModels()
    {
        return $this->_optionViewModels;
    }

    /**
     * @param array $optionViewModels
     */
    public function setOptionViewModels($optionViewModels)
    {
        $this->_optionViewModels = $optionViewModels;
    }


    /**
     * @param \Magento\Bundle\Model\Option $option
     * @return string
     */
    public function getProductOptionsHtml(\Magento\Bundle\Model\Option $option)
    {
        $type = $option->getType();
        $templates = $this->getOptionViews();
        $classes = $this->getOptionViewModels();

        if (!isset($templates[$type]) || !isset($classes[$type])) {
            return '';
        }

        try {
            /** @var \Magento\Framework\View\LayoutInterface $layout */
            $layout = $this->getLayout();
            $optionBlock = $layout->createBlock($classes[$type]);
            $optionBlock->setTemplate($templates[$type]);
            $optionBlock->setProduct($this->getProduct());
            $optionBlock->setOption($option);

            return $optionBlock->toHtml();
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Reset options to query again
     * @return $this
     */
    public function resetProductOptions()
    {
        $this->options = false;
        return $this;
    }

    /**
     * Get formed config data from calculated options data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $options
     * @return array
     */
    protected function getConfigData(\Magento\Catalog\Model\Product $product, array $options)
    {
        $isFixedPrice = $this->getProduct()->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED;

        $productAmount = $product
            ->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
            ->getPriceWithoutOption();

        $baseProductAmount = $product
            ->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
            ->getAmount();

        $config = [
            'options' => $options,
            'selected' => $this->selectedOptions,
            'positions' => $this->optionsPosition,
            'bundleId' => $product->getId(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $isFixedPrice ? $baseProductAmount->getValue() : 0
                ],
                'basePrice' => [
                    'amount' => $isFixedPrice ? $productAmount->getBaseAmount() : 0
                ],
                'finalPrice' => [
                    'amount' => $isFixedPrice ? $productAmount->getValue() : 0
                ]
            ],
            'priceType' => $product->getPriceType(),
            'isFixedPrice' => $isFixedPrice,
        ];

        return $config;
    }

    /**
     * Get formed data from selections of option
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getSelections(
        \Magento\Bundle\Model\Option $option,
        \Magento\Catalog\Model\Product $product
    ) {

        $selections = [];
        $selectionCount = count($option->getSelections());

        foreach ($option->getSelections() as $selectionItem) {
            /* @var $selectionItem Magento\Catalog\Model\Product */
            $selectionId = $selectionItem->getSelectionId();
            $selections[$selectionId] = $this->getSelectionItemData($product, $selectionItem);

            if (($selectionItem->getIsDefault() || $selectionCount == 1 && $option->getRequired())
                && $selectionItem->isSalable()
            ) {
                $this->selectedOptions[$option->getId()][] = $selectionId;
            }
        }

        return $selections;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $selection
     * @return array|\Magento\Catalog\Model\Product
     */
    protected function getSelectionItemData(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\Product $selection
    ) {

        $qty = ($selection->getSelectionQty() * 1) ?: '1';

        $optionPriceAmount = $product->getPriceInfo()
                                     ->getPrice(\Magento\Bundle\Pricing\Price\BundleOptionPrice::PRICE_CODE)
                                     ->getOptionSelectionAmount($selection);
        $finalPrice = $optionPriceAmount->getValue();
        $basePrice = $optionPriceAmount->getBaseAmount();

        $oldPrice = $product->getPriceInfo()
                            ->getPrice(\Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::PRICE_CODE)
                            ->getOptionSelectionAmount($selection)
                            ->getValue();

        $selection = [
            'qty' => $qty,
            'customQty' => $selection->getSelectionCanChangeQty(),
            'optionId' => $selection->getId(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $oldPrice,
                ],
                'basePrice' => [
                    'amount' => $basePrice,
                ],
                'finalPrice' => [
                    'amount' => $finalPrice,
                ],
            ],
            'priceType' => $selection->getSelectionPriceType(),
            'tierPrice' => $this->getTierPrices($product, $selection),
            'name' => $selection->getName(),
            'canApplyMsrp' => false,
        ];

        return $selection;
    }

    /**
     * Get formed data from option
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param \Magento\Catalog\Model\Product $product
     * @param int $position
     * @return array
     */
    protected function getOptionItemData(
        \Magento\Bundle\Model\Option $option,
        \Magento\Catalog\Model\Product $product,
        $position
    ) {

        return [
            'selections' => $this->getSelections($option, $product),
            'title' => $option->getTitle(),
            'isMulti' => in_array($option->getType(), ['multi', 'checkbox']),
            'position' => $position
        ];
    }
}
