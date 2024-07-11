<?php
namespace Smartosc\Checkout\Model\Quote;

use Magento\Framework\Exception\LocalizedException;

class CustomCart
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $cartItemCollectionFactory;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * @var int
     */
    protected $customerId = null;
    
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;
    
    /**
     * @var  \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;
    
    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory $quoteItemModelFactory
     */
    protected $quoteItemModelFactory;
    
    
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $cartItemCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemModelFactory
    ) {
        $this->cartItemCollectionFactory = $cartItemCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productHelper = $productHelper;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->quoteItemModelFactory = $quoteItemModelFactory;
    }
    
    public function setCustomerId($customerId)
    {
        
        $this->customerId = $customerId;
        
        return $this;
    }
    
    public function getProductCollection()
    {
        return $collection = $this->productCollectionFactory->create();
    }
    
    private function handleEmptyCustomerId()
    {
        if (!$this->customerId) {
            throw new LocalizedException(__("Customer ID is not provide. Cannot proceed! Please enter customer ID"));
        }
    }
    
    public function removeInvisibleProductFromActiveCart()
    {
        
        $this->handleEmptyCustomerId();
        
        $activeCarts = $this->getActiveCarts();
        
        foreach ($activeCarts as $cart) {
            $quoteId = $cart->getEntityId();
            
            $collection = $this->cartItemCollectionFactory->create()->addFieldToFilter('quote_id', $quoteId);
            
            foreach ($collection as $item) {
                if (!$this->isVisibleProduct((int)$item->getData('product_id'))) {
                    $cart->removeItem((int)$item->getData('item_id'))->save();
                    // display message name
                }
            }
        }
    }
    
    
    public function getActiveCarts()
    {
        $customerId = $this->customerId;
        $this->handleEmptyCustomerId();
        
        $collection = $this->quoteCollectionFactory->create()
                       //->addFieldToSelect('quote_id')
                       ->addFieldToFilter('customer_id', $customerId)
                       ->addFieldToFilter('is_active', 1);
        
        return $collection;
    }
    
    protected function isVisibleProduct($productId)
    {
        
        return $this->productHelper->canShow($productId);
    }
}
