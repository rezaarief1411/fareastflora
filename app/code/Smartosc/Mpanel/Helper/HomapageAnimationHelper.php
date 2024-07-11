<?php

namespace Smartosc\Mpanel\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class HomapageAnimationHelper
 * @package Smartosc\Mpanel\Helper
 */
class HomapageAnimationHelper extends \MGS\Mpanel\Helper\Data
{
    const HOME_ANIMATION = "mpanel/config_home_animation_effect/enable";

    /**
     * @param int|null $store
     *
     * @return string
     */
    public function getAnimationConfig($store = null)
    {
        return $this->getStoreConfig(self::HOME_ANIMATION);
    }
}
