<?php

namespace Smartosc\CustomBundleProduct\Block\Render;

use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class BundleRender
 * @package Smartosc\CustomBundleProduct\Block\Render
 */
class BundleRender extends \Magento\Bundle\Block\Checkout\Cart\Item\Renderer
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @param $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $this->setTemplate("Smartosc_CustomBundleProduct::cart/item/default.phtml");

        return parent::_toHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->_product;
    }


    /**
     * @param AbstractItem $item
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRowTotalHtmlForItem(
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        \Magento\Catalog\Model\Product $product
    ) {
        $block = $this->getLayout()->getBlock('checkout.item.price.row');

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $collection */
        $collection = $item->getResourceCollection();
        $collection->addFieldToFilter('parent_item_id', $item->getId())
            ->addFieldToFilter('product_id', $product->getId());

        /** @var \Magento\Quote\Model\Quote\Item $subItem */
        $subItem = $collection->getFirstItem();
        $subItem->setQuote($item->getQuote());
        $block->setItem($subItem);

        return $block->toHtml();
    }


    /**
     * {@inheritdoc}
     */
    public function getProductUrl()
    {
        $product = $this->getProduct();

        return $product->getUrlModel()->getUrl($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductForThumbnail()
    {
        return $this->getProduct();
    }



    /**
     * @return \Magento\Framework\App\ObjectManager
     */
    private function getObjectManager()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $this->objectManager;
    }
}
