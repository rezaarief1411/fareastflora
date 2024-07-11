<?php


namespace Smartosc\CustomBundleProduct\Model\ResourceModel\PotImage;


class Image extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TBL_BUNDLEPOT_IMAGE = 'bundle_pot_image';
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TBL_BUNDLEPOT_IMAGE, 'id');
    }
}
