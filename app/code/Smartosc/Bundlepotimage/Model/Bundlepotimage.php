<?php

// @codingStandardsIgnoreFile

namespace Smartosc\Bundlepotimage\Model;

/**
 * Class Bundlepotimage
 * @package Smartosc\Bundlepotimage\Model
 */
class Bundlepotimage extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smartosc\Bundlepotimage\Model\ResourceModel\Bundlepotimage');
    }
}
