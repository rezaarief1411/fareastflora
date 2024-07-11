<?php

namespace Smartosc\Preorder\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Data
 * @package Smartosc\Preorder\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_MESSAGE_VALUE = 'catalog/display/message';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param Session $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context                                              $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Checkout\Model\Session                      $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
    )
    {
        $this->timezone = $timezone;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @param $product
     * @return bool
     */
    public function isPreoderProduct($product)
    {
        $enablePreorder = false;
        $now = $this->timezone->date();
        $availableFromDay = $now;
        if ($product->getCustomAttribute('available_from_date')) {
            $availableFromDay = $this->timezone->date($product->getCustomAttribute('available_from_date')->getValue());
        }
        if ($product->getCustomAttribute('is_preorder')) {
            $isPreorderProduct = $product->getCustomAttribute('is_preorder')->getValue();
            $enablePreorder = $isPreorderProduct && $availableFromDay > $now;
        }
        return $enablePreorder;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_MESSAGE_VALUE, $storeScope);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPreorderButtonLabel()
    {
        return __("Preorder");
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $format
     * @return string
     */
    public function getAvailableFromDay($product, $format = 'd M Y')
    {
        $availableFromDay = $this->timezone->date();
        if ($product->getCustomAttribute('available_from_date')) {
            $date = $product->getCustomAttribute('available_from_date')->getValue();
            $availableFromDay = new \DateTime($date);
        }
        return $availableFromDay->format($format);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFarthestPreorderShippingDay()
    {
        $farthestDay = $this->timezone->date();
        if ($this->checkoutSession->getQuote()) {
            $allItems = $this->checkoutSession->getQuote()->getItems();
            if (!empty($allItems)) {
                foreach ($allItems as $item) {
                    $productId = $item->getProductId();
                    if ($productId) {
                        $product = $this->productRepository->getById($productId);
                        $isPreorder = $product->getIsPreorder();
                        if ($isPreorder) {
                            $availableFromDay = $this->timezone->date($product->getAvailableFromDate());
                            if ($availableFromDay > $farthestDay) {
                                $farthestDay = $availableFromDay;
                            }
                        }
                    }
                }
            }
        }
        return $farthestDay->format('d-m-Y');
    }
}
