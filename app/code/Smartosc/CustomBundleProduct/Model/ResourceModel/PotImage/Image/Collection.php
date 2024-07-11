<?php


namespace Smartosc\CustomBundleProduct\Model\ResourceModel\PotImage\Image;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritDoc}}
     */
    protected function _construct()
    {
        $this->_init(
            \Smartosc\CustomBundleProduct\Model\PotImage\Image::class,
            \Smartosc\CustomBundleProduct\Model\ResourceModel\PotImage\Image::class
        );

    }
}
