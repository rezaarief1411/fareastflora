<?php

namespace Smartosc\Customer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Smartosc\Customer\Model\PhoneNumberByCountry;

class Data extends AbstractHelper
{

    const STOREFRONT_CONFIG = 'smartosc_storefront_configuration';
    const STOREFRONT_GENERAL_CONFIG = 'general';
    const STOREFRONT_GENERAL_DOB_TOOLTIP_LABEL_CONFIG = 'dob_tooltip_label';
    const STOREFRONT_GENERAL_CONTACT_NUMBER_TOOLTIP_LABEL_CONFIG = 'contact_number_tooltip_label';

    /**
     * @var PhoneNumberByCountry
     */
    public $phoneNumberByCountry;
    /**
     * @var \Magento\Directory\Block\Data
     */
    public $directoryHelper;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        Context         $context,
        PhoneNumberByCountry $phoneNumberByCountry,
        \Magento\Directory\Block\Data $directoryHelper,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->phoneNumberByCountry = $phoneNumberByCountry;
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
    }

    public function getCountryCodeOptions()
    {
        return $this->phoneNumberByCountry->getCountries();
    }

    public function getGeneralConfigValue($fieldName)
    {
        return $this->scopeConfig->getValue(
            self::STOREFRONT_CONFIG.'/'.self::STOREFRONT_GENERAL_CONFIG .'/'. $fieldName,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getDobTooltipLabel()
    {
        return $this->getGeneralConfigValue(self::STOREFRONT_GENERAL_DOB_TOOLTIP_LABEL_CONFIG);
    }
    public function getContactNumberTooltipLabel()
    {
        return $this->getGeneralConfigValue(self::STOREFRONT_GENERAL_CONTACT_NUMBER_TOOLTIP_LABEL_CONFIG);
    }

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        return $this->directoryHelper->getCountryHtmlSelect();
    }

    public function getTermAndConditionUrl()
    {
        return $this->_urlBuilder->getUrl('terms-and-conditions');
    }

    public function getPrivacyPoliciesUrl()
    {
        return $this->_urlBuilder->getUrl('privacy-policies');
    }
}
