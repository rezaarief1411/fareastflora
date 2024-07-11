<?php

namespace Smartosc\Checkout\Controller\Customer\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Smartosc\Checkout\Model\SmartGift\GiftManagement;

/**
 * Class AddGiftToCart
 * @package Smartosc\Checkout\Controller\Customer\Ajax
 */
class AddGiftToCart extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var CategoryFactory
     */

    protected $categoryFactory;

    /**
     * @var ScopeConfigInterface
     */

    protected $scopeConfig;

    /**
     * @var GiftManagement
     */
    protected $giftManagement;

    /**
     * AddGiftToCart constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param GiftManagement $giftManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Smartosc\Checkout\Model\SmartGift\GiftManagement $giftManagement,
        ScopeConfigInterface $scopeConfig,
        CategoryFactory $categoryFactory
    ) {
        $this->giftManagement = $giftManagement;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
        try {
            $this->quote = $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }

        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $httpBadRequestCode = 400;
        $response = [
            'errors' => false,
            'message' => ''
        ];

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        if ($this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        try {
            /** @var GiftManagement $giftManagement */
            $giftManagement = $this->giftManagement;
            $giftManagement->save();
            $messages = $giftManagement->getMessages();
            $message = implode('<br/>', $messages);
            $response = [
                'errors' => false,
                'message' => $message
            ];
        } catch (\Exception $exception) {
            $response = [
              'errors' => true,
              'message' => $exception->getMessage()
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);
    }
}
