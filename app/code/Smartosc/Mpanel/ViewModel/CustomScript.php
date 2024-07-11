<?php

namespace Smartosc\Mpanel\ViewModel;

/**
 * Class CustomScript
 * @package Smartosc\Mpanel\ViewModel
 */
class CustomScript implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /** @var \MGS\Mpanel\Helper\Data */
    protected $helper;

    const CUSTOM_SCRIPT = 'mpanel/custom_scripts/custom_script';

    /**
     * CustomScript constructor.
     * @param \MGS\Mpanel\Helper\Data $helper
     */
    public function __construct(
        \MGS\Mpanel\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }


    /**
     * @return string
     */
    public function getGoogleTagScript()
    {
        $configKey = self::CUSTOM_SCRIPT;

        return $this->helper->getStoreConfig($configKey);
    }
}
