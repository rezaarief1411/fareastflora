<?php

namespace Smartosc\Checkout\Observer;

/**
 * Class AddAddonProductCart
 * @package Smartosc\Checkout\Observer
 */
class AddAddonProductCart implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory
     */
    protected $relationFactory;
    
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * AddAddonProductCart constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory $relationFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Smartosc\Checkout\Model\Quote\BundleAddon\RelationFactory $relationFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->relationFactory = $relationFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $relation = $this->checkoutSession->getSmartRelationSession();

        if ($relation) {

            $bundleId = array_keys($relation)[0];
            $realRelation = $relation[$bundleId];

            $productIdItemIdMap = $realRelation['addon'];
            $ids = array_keys($productIdItemIdMap);
            $lastId = $ids[count($productIdItemIdMap) - 1];

            $quote = $this->checkoutSession->getQuote();
            $items = $quote->getAllItems();

            foreach ($items as $quoteItem) {
                $productId = $quoteItem->getProductId();
                if ($productId == $bundleId) {
                    $relation = array_replace_recursive($relation,
                        [
                            $productId => [
                                'item_id' => $quoteItem->getItemId()
                            ]
                        ]);
                }
                if (isset($productIdItemIdMap[$productId])) {
                    $productIdItemIdMap[$productId] = $quoteItem->getId();
                }
                if($productId == $lastId)
                    break;
            }

            $bundleItemId = $relation[$bundleId]['item_id'];
            foreach ($productIdItemIdMap as $productId => $itemId) {
                $model = $this->relationFactory->create();
                $model->addData([
                    'main_product_id' => $bundleId,
                    'quote_id' => $this->checkoutSession->getQuoteId(),
                    'product_addon_id' => $productId,
                    'last_quote_item_id' => $bundleItemId,
                    'addon_item_id' => $itemId
                ])
                    ->getResource()
                    ->save($model);
            }

            $this->checkoutSession->unsSmartRelationSession();
        }
    }
}
