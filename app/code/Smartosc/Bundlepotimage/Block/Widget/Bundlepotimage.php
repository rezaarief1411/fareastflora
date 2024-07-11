<?php

namespace Smartosc\Bundlepotimage\Block\Widget;

/**
 * Class Bundlepotimage
 * @package Smartosc\Bundlepotimage\Block\Widget
 */
class Bundlepotimage extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
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
    public function getBundlepotimageById($id)
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
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'bundlepotimage/'.$bundlepotimage->getFilename();
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
