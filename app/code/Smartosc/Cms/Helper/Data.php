<?php

namespace Smartosc\Cms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package Smartosc\Cms\Helper
 */
class Data extends AbstractHelper
{
    const ENABLE_HIDE_DELETE_OPTION_IN_CMS = "smartosc_general/cms_setting/enabled_hide_delete_option";

    /**
     * @return mixed
     */
    public function isEnableHideDeleteOption()
    {
        return $this->scopeConfig->getValue(self::ENABLE_HIDE_DELETE_OPTION_IN_CMS);
    }
}
