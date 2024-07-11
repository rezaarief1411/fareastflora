<?php

namespace Smartosc\CustomLinked\Model;

/**
 * Class Product
 *
 * Class Product extends \Magento\Catalog\Model\Product
 */
class Product extends \Magento\Catalog\Model\Product
{
    const LINK_TYPE_CUSTOMLINKED = 17;

    /**
     * @return mixed
     */
    public function getCustomlinkedProducts()
    {
        if (!$this->hasCustomlinkedProducts()) {
            $products = [];
            $collection = $this->getCustomlinkedProductCollection();
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setCustomlinkedProducts($products);
        }
        return $this->getData('customlinked_products');
    }

    /**
     * @return array
     */
    public function getCustomlinkedProductIds()
    {
        if (!$this->hasCustomlinkedProductIds()) {
            $ids = [];
            foreach ($this->getCustomlinkedProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setCustomlinkedProductIds($ids);
        }
        return [$this->getData('customlinked_product_ids')];
    }
    
    /**
     * @return $this
     */
    public function getCustomlinkedProductCollection()
    {
        $collection = $this
            ->getLinkInstance()
            ->setLinkTypeId(static::LINK_TYPE_CUSTOMLINKED)
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($this);
        return $collection;
    }
}
