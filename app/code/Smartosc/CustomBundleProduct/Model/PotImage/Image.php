<?php


namespace Smartosc\CustomBundleProduct\Model\PotImage;


class Image extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritDoc}}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Smartosc\CustomBundleProduct\Model\ResourceModel\PotImage\Image::class);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData('created_at', $createdAt);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData('updated_at', $updatedAt);
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }
}
