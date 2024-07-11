<?php

namespace Smartosc\Checkout\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ExtendConfigProvider
 * @package Smartosc\Checkout\Plugin
 */
class ExtendConfigProvider
{
    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $configurationPool;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $quoteItemRepository;

    /**
     * @var \Smartosc\Checkout\Helper\Product\Price\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $quoteItemCollectionFactory;

    /**
     * @var \Smartosc\Checkout\Helper\Product\Data
     */
    protected $productHelper;

    /**
     * @var \Magento\Quote\Model\Cart\Totals\ItemConverter
     */
    private $itemConverter;

    /**
     * ExtendConfigProvider constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $quoteItemRepository
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Smartosc\Checkout\Helper\Product\Price\Data $priceHelper
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param \Smartosc\Checkout\Helper\Product\Data $productHelper
     * @param \Magento\Quote\Model\Cart\Totals\ItemConverter $itemConverter
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Quote\Api\CartItemRepositoryInterface $quoteItemRepository,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Smartosc\Checkout\Helper\Product\Price\Data $priceHelper,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Smartosc\Checkout\Helper\Product\Data $productHelper,
        \Magento\Quote\Model\Cart\Totals\ItemConverter $itemConverter
    ) {
        $this->productHelper = $productHelper;
        $this->checkoutSession = $checkoutSession;
        $this->imageHelper = $imageHelper;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->configurationPool = $configurationPool;
        $this->priceHelper = $priceHelper;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->itemConverter = $itemConverter;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $result)
    {

        $output = $result;

        $quoteItemData = $this->getQuoteItemData();
        $activeQuote = $this->checkoutSession->getQuote();
        $output['quoteItemData'] = $quoteItemData;

        // custom totalsData items
        $totalsData = &$output['totalsData'];
        foreach ($totalsData['items'] as $index => $item) {
            $itemId = $item['item_id']; // quote id
            $productData = $this->getProductDataByQuoteItemId($itemId);
            //$productSku = $productData['sku'];
            $productSku = $productData['skuOfSimpleProduct'];
            $productId = $productData['product_id'];
            $totalsData['items'][$index]['sku'] = $productSku;
            $totalsData['items'][$index]['product_id'] = $productId;
            $totalsData['items'][$index]['product_type'] = $productData['product_type'];

            if ($productData['product_type'] == 'bundle') {
                // get smart_options
                $items = [];
                foreach ($activeQuote->getItemsCollection() as $quoteItem) {
                    if (!$quoteItem->isDeleted() && $quoteItem->getParentItemId()
                        && $quoteItem->getParentItemId() == $itemId && $quoteItem->getParentItem()) {
                        try {
                            /** @var \Magento\Quote\Model\Cart\Totals\Item $totalsItemInterface */
                            $totalsItemInterface = $this->itemConverter->modelToDataObject($quoteItem);
                            $temp = $totalsItemInterface->__toArray();
                            $temp['sku'] = $quoteItem->getSku();
                            $items[] = $temp;
                        } catch (\Exception $e) {
                        }
                    }
                }

                $totalsData['items'][$index]['smart_options'] = $items;
            }

            try {
                $productOldPrice = $this->priceHelper->getRegularPriceBySku($productSku);
            } catch (NoSuchEntityException $e) {
                $productOldPrice = false;
            }
            $productOldPriceText = $productOldPrice ? sprintf("%.2f", $productOldPrice) : '';
            $totalsData['items'][$index]['old_price'] = $productOldPriceText;
            $swatchData = $item['options'];

            $swatchDataDecoded = json_decode($swatchData, true);
            $hashColor = null;
            $newOptions = ['label' => 'Colour'];

            if (is_array($swatchDataDecoded) && count($swatchDataDecoded) > 0) {
                $swatchValue = $swatchDataDecoded[0]['value'];
                $optionId = $this->productHelper->getAttributeSwatchOptionId($productId, $swatchValue);
                $hashColor = $this->productHelper->getAttributeSwatchHasCode($optionId);
                $newOptions['value']= $hashColor;
                // change x.options
                if (!is_null($hashColor)) {
                    $totalsData['items'][$index]['options'] = json_encode([$newOptions]);
                }
            }
        }

        return $output;
    }

    protected function getProductDataByQuoteItemId($quoteItemId)
    {
        $rs = null;
        $quoteItemCollection = $this->quoteItemCollectionFactory->create();

        $quoteItemCollection->addFieldToFilter('item_id', $quoteItemId);

        foreach ($quoteItemCollection as $item) {

            /*@var \Magento\Quote\Model\Quote\Item $item*/
            $rs = [
                'sku' => $item->getSku(), // sku of quote item
                'skuOfSimpleProduct' => trim($item->getProduct()->getData('sku'), " \"\t."),
                'product_id' => $item->getProductId(),
                'product_type' => $item->getProductType()
            ];
        }

        return $rs;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuoteItemData()
    {

        $quoteItemData = [];
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);

            foreach ($quoteItems as $index => $quoteItem) {
                $originalPrice = $this->priceHelper->getRegularPrice($quoteItem->getProduct());
                $quoteItemData[$index] = $quoteItem->toArray();
                $quoteItemData[$index]['options'] = $this->getFormattedOptionValue($quoteItem);
                $quoteItemData[$index]['thumbnail'] = $this->imageHelper->init(
                    $quoteItem->getProduct(),
                    'product_thumbnail_image'
                )->getUrl();
                $quoteItemData[$index]['price_origin'] = $originalPrice;
                $quoteItemData[$index]['message'] = $quoteItem->getMessage();
            }
        }

        return $quoteItemData;
    }

    /**
     * @param $item
     * @return array
     */
    protected function getFormattedOptionValue($item)
    {
        $optionsData = [];
        $options = $this->configurationPool->getByProductType($item->getProductType())->getOptions($item);
        foreach ($options as $index => $optionValue) {
            /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
            $helper = $this->configurationPool->getByProductType('default');
            $params = [
                'max_length' => 55,
                'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
            ];
            $option = $helper->getFormattedOptionValue($optionValue, $params);
            $optionsData[$index] = $option;
            $optionsData[$index]['label'] = $optionValue['label'];
        }

        return $optionsData;
    }
}
