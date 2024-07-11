<?php

namespace Smartosc\Bundlepotimage\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Bundlepotimage extends Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->_objectManager->create(\Smartosc\Bundlepotimage\Model\Bundlepotimage::class);
    }

    /**
     * @param $id
     */
    public function getBundlepotimageId($id)
    {
        if (!is_numeric($id)) {
            $bundlepotimage = $this->getModel()->getCollection()->addFieldToFilter('identifier', $id)->getFirstItem();
            if ($bundlepotimage->getId()) {
                return $bundlepotimage;
            } else {
                return;
            }
        } else {
            $bundlepotimage = $this->getModel()->load($id);
            return $bundlepotimage;
        }
    }

    /**
     * @param $bundlepotimage
     *
     * @return string
     */
    public function getBundlepotImageUrl($bundlepotimage)
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'bundlepotimage'.$bundlepotimage->getBundlePotImage();
    }

    /**
     * @param $bundlepotimage
     *
     * @return string
     */
    public function getCustomClass($bundlepotimage)
    {
        $class = '';
        if ($bundlepotimage->getCustomClass()!='') {
            $class .= ' '.$bundlepotimage->getCustomClass();
        }
        if ($bundlepotimage->getEffect()!='') {
            $class .= ' '.$bundlepotimage->getEffect();
        }
        return $class;
    }
}
