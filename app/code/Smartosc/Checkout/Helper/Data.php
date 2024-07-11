<?php

namespace Smartosc\Checkout\Helper;

class Data extends \MGS\Mpanel\Helper\Data
{
    const PICKUP_TIME_CONTENT = 'smartosc_general/pickup_time/content';

    const BLOCK_DAYS = 'smartosc_general/block_day/value';
    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategoryModel()
    {
        return $this->_objectManager->create(\Magento\Catalog\Model\Category::class);
    }

    /**
     * @return mixed
     */
    public function getPickupTimeContent(){
        return $this->scopeConfig->getValue(self::PICKUP_TIME_CONTENT);
    }

    /**
     * @return mixed
     */
    public function getBlockDays(){
        return $this->scopeConfig->getValue(self::BLOCK_DAYS);
    }
}

