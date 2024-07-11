<?php

namespace Smartosc\CustomBundleProduct\Model\BundleProduct;

/**
 * Class Option
 * @package Smartosc\CustomBundleProduct\Model\BundleProduct
 */
class Option
{

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * Option constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(\Magento\Framework\Serialize\Serializer\Json $serializer)
    {
        $this->serializer = $serializer;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getProductOption($item)
    {
        $result = [];
        $product = $item->getProduct();

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption
            ? $this->serializer->unserialize($optionsQuoteItemOption->getValue())
            : [];

        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);
        // get and add bundle selections collection
        $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

        $bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());
        if (!empty($bundleSelectionIds)) {
            $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $bundleSelections = $bundleOption->getSelections();
                    $result[] = $bundleSelections;
                }
            }

        }

        return $result;
    }
}
