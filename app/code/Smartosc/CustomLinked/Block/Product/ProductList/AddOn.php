<?php

namespace Smartosc\CustomLinked\Block\Product\ProductList;

/**
 * Class AddOn
 *
 * class AddOn extends \Magento\Catalog\Block\Product\ProductList\Upsell
 */
class AddOn extends \Magento\Catalog\Block\Product\ProductList\Upsell
{
    /**
     * @var \Smartosc\CustomLinked\Model\ProductFactory
     */
    protected $productExtFactory;

    /**
     * AddOn constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Smartosc\CustomLinked\Model\ProductFactory $productExtFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Smartosc\CustomLinked\Model\ProductFactory $productExtFactory,
        array $data = []
    ) {
        $this->productExtFactory = $productExtFactory;
        
        parent::__construct(
            $context,
            $checkoutCart,
            $catalogProductVisibility,
            $checkoutSession,
            $moduleManager
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareData()
    {
        $product = $this->getProduct();
        /* @var $product \Magento\Catalog\Model\Product */
        
        $product = $this->productExtFactory->create()->load($product->getId());
        
        $this->_itemCollection = $product->getCustomlinkedProductCollection()->setPositionOrder()->addStoreFilter();
        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        
        $this->_itemCollection->load();
        
        /**
         * Updating collection with desired items
         */
        $this->_eventManager->dispatch(
            'catalog_product_addon',
            ['product' => $product, 'collection' => $this->_itemCollection, 'limit' => null]
        );
        
        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        
        return $this;
    }

    /**
     * @return string
     */
    public function getProductType() {
        $product = $this->getProduct();

        return $product->getTypeId();
    }
}
