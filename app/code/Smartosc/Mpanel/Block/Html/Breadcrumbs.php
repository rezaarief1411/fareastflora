<?php

namespace Smartosc\Mpanel\Block\Html;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

/**
 * Class Breadcrumbs
 * @package Smartosc\Mpanel\Block\Html
 */
class Breadcrumbs extends \Magento\Theme\Block\Html\Breadcrumbs
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \MGS\Mpanel\Helper\Data
     */
    protected $helper;

    const BANNER_MEDIA_PATH      = 'wysiwyg/banner';

    const PAGE_CHECKOUT          = 'checkout_index_index';
    const PAGE_CART              = 'checkout_cart_index';
    const PAGE_LOGIN_REGISTER    = 'customer_account_login';
    const PAGE_FORGOT_PASSWORD   = 'customer_account_forgotpassword';
    const PAGE_MY_ACCOUNT        = 'customer_account_index';
    const MY_ACCOUNT_EDIT        = 'customer_account_edit';

    const CONFIG_FORGOT_PASSWORD = 'mpanel/banner/forgot_password';
    const CONFIG_CART            = 'mpanel/banner/cart';
    const CONFIG_LOGIN           = 'mpanel/banner/login';
    const CONFIG_CHECKOUT        = 'mpanel/banner/checkout';
    const CONFIG_MY_ACCOUNT      = 'mpanel/banner/my_account';

    /**
     * Breadcrumbs constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \MGS\Mpanel\Helper\Data $helper
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \MGS\Mpanel\Helper\Data $helper,
        array $data = [],
        Json $serializer = null
    ) {
        $this->helper = $helper;
        $this->request = $request;
        parent::__construct($context, $data, $serializer);
    }


    /**
     * @return string
     */
    private function getFullActionName()
    {
        return $this->request->getFullActionName();
    }

    /**
     * @param bool $isRelativeUrl
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return string
     */
    public function getPageBanner($isRelativeUrl = false)
    {
        $image = '';
        $store = $this->_storeManager->getStore();
        $mediaBaseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $storeId = $this->helper->getStore()->getId();
        $layoutHandler = $this->getFullActionName();
        switch ($layoutHandler) {
            case self::PAGE_CHECKOUT:
                $image = $this->helper->getStoreConfig(self::CONFIG_CHECKOUT, $storeId);
                break;
            case self::PAGE_LOGIN_REGISTER:
                $image = $this->helper->getStoreConfig(self::CONFIG_LOGIN, $storeId);
                break;
            case self::PAGE_FORGOT_PASSWORD:
                $image = $this->helper->getStoreConfig(self::CONFIG_FORGOT_PASSWORD, $storeId);
                break;
            case self::PAGE_CART:
                $image = $this->helper->getStoreConfig(self::CONFIG_CART, $storeId);
                break;
            case self::PAGE_MY_ACCOUNT && self::MY_ACCOUNT_EDIT:
                $image = $this->helper->getStoreConfig(self::CONFIG_MY_ACCOUNT, $storeId);
                break;
            default:
        }

        if ($image) {
            if (!$isRelativeUrl) {
                $url = $mediaBaseUrl .
                    ltrim(self::BANNER_MEDIA_PATH)
                    . '/'
                    . $image;
            } else {
                $url = $image;
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while getting the image url.')
            );
        }

        return $url;
    }
}
