<?php

namespace Smartosc\Checkout\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Main extends Template
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $data);
    }

    public function getLocaleTimezone()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $localeTimezone = $this->scopeConfig->getValue('general/locale/timezone', ScopeInterface::SCOPE_STORE, $storeId);

        return $localeTimezone;
    }

    public function getAuthorizeMessage()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $authorizeMessage = $this->scopeConfig->getValue('smartosc_authorize/authorize_settings/authorize_message', ScopeInterface::SCOPE_STORE, $storeId);

        return $authorizeMessage;
    }
}