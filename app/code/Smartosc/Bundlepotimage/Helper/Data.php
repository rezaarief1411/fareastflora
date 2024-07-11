<?php

namespace Smartosc\Bundlepotimage\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Smartosc\Bundlepotimage\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Smartosc\Bundlepotimage\Model\ResourceModel\Bundlepotimage\CollectionFactory
     */
    protected $imageCollectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Smartosc\Bundlepotimage\Model\ResourceModel\Bundlepotimage\CollectionFactory $imageCollectionFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        \Smartosc\Bundlepotimage\Model\ResourceModel\Bundlepotimage\CollectionFactory $imageCollectionFactory,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->imageCollectionFactory = $imageCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @param $selectionId
     *
     * @return string
     */
    public function getImageUrl($selectionId) {
        $url='';
        $collection = $this->imageCollectionFactory->create();
        $collection->addFieldToFilter('selection_id', $selectionId);

        foreach ($collection as $item) {
            $image = $item->getData('bundle_pot_image');
            $url = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'bundlepotimage'.$image;
            break;
        }

        return $url;
    }
}
