<?php

namespace Smartosc\CustomLinked\Model\ProductLink\CollectionProvider;

/**
 * Class CustomLinked
 *
 * Custom CustomLinked CollectionProvider
 */
class CustomLinked implements \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getLinkedProducts($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();   
        $product = $objectManager->get('\Smartosc\CustomLinked\Model\Product');

        return $product->getCustomlinkedProducts();
    }
}
