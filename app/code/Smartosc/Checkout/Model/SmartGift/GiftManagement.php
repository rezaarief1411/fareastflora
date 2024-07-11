<?php

namespace Smartosc\Checkout\Model\SmartGift;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManager;

/**
 * Class GiftManagement
 * @package Smartosc\Checkout\Model\SmartGift
 */
class GiftManagement
{
    /**
     * @var int[]
     */
    protected $productInCart;

    /**
     * @var int[]
     */
    protected $giftsAvailable;

    /**
     * @var int[]
     */
    protected $gitsChosen;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * GiftManagement constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManager $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryFactory $categoryFactory
     * @param RequestInterface $request
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManager $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        CategoryFactory $categoryFactory,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->categoryFactory = $categoryFactory;
        try {
            $this->quote = $checkoutSession->getQuote();
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getGiftsToBeAdd()
    {
        $rs = [];

        // check if cart has gift
        $hasGift = $this->getHasGift();
        $giftChosen = $this->getChosenGiftProductIds();


        if (count($giftChosen) > count($hasGift)) {
            $rs = array_diff($giftChosen, $hasGift);
        }

        return $rs;
    }

    /**
     * return products belonging to gift category and they have been added to cart
     *
     * @return array
     */
    protected function getHasGift()
    {
        $productInCart = $this->getInCartProductIds();
        $giftAvailable = $this->getGiftProductIds();

        return array_intersect($giftAvailable, $productInCart);
    }

    /**
     * to be removed gifts
     *
     * @return array
     */
    public function getGiftsToBeRemove()
    {
        $rs = [];
        $giftChosen = $this->getChosenGiftProductIds();
        // check if cart has gift
        $hasGift = $this->getHasGift();

        if (count($giftChosen) < count($hasGift)) {
            foreach ($hasGift as $item) {
                if (!in_array($item, $giftChosen)) {
                    $rs[] = $item;
                }
            }
        }
        return $rs;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addGift()
    {
        $messages = [];
        $ids = $this->getGiftsToBeAdd();
        $storeId = $this->storeManager->getStore()->getId();

        foreach ($ids as $id) {
            $product = $this->productRepository->getById($id, false, $storeId);
            $this->quote->addProduct($product);
            $messages[] = __('Added %1 successfully!', $product->getName());
        }

        $this->messages = array_merge($this->messages, $messages);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function removeGift()
    {
        $messages = [];
        $ids = $this->getGiftsToBeRemove();

        foreach ($ids as $item) {
            if ($quoteItemId = $this->_getQuoteItem($item)) {
                $productName = $this->productRepository->getById($item)->getName();
                $this->quote->removeItem($quoteItemId)->save();
                $messages[] = __('Removed %1 successfully!', $productName);
            }

        }

        $this->messages = array_merge($this->messages, $messages);

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @throws \Exception
     */
    public function save()
    {

        $this->addGift();
        $this->removeGift();
        $this->quote->collectTotals()->save();
    }


    /**
     * @return array
     */
    protected function getGiftProductIds()
    {
        $categoryGiftId = $this->scopeConfig->getValue('mpanel/category_gift/gift_category');
        /** @var  \Magento\Catalog\Model\Category $productsGift */
        $categoryGift = $this->categoryFactory->create()->load($categoryGiftId);
        /** @var Product[] $productsGift */
        $productsGift = $categoryGift->getProductsPosition();

        return array_keys($productsGift);
    }


    /**
     * products, which have been added to cart
     * @return array
     */
    protected function getInCartProductIds()
    {
        $quoteItems = $this->quote->getItemsCollection();

        $result = [];
        foreach ($quoteItems as $item) {
            $result[] = $item->getProductId();
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getChosenGiftProductIds()
    {
        $giftPostData = $this->request->getParam('data');

        $result = [];
        if ($giftPostData) {
            foreach ($giftPostData as $giftItem) {
                $result[] = $giftItem['id'];
            }
        }

        return $result;
    }

    /**
     * @param $productId
     * @return bool
     */
    protected function _getQuoteItem($productId)
    {
        $quote = $this->quote;
        $quoteItems = $quote->getItemsCollection();
        foreach ($quoteItems as $item) {
            $productInQuote = $item->getProductId();
            if ($productInQuote == $productId) {
                $quoteItemId = $item->getItemId();
                return $quoteItemId;
            }
        }

        return false;
    }
}
