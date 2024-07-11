<?php

namespace Smartosc\Mpanel\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FallingFlowerHelper
 * @package Smartosc\Mpanel\Helper
 */
class FallingFlowerHelper extends AbstractHelper
{
    const UPLOAD_DIR = 'wysiwyg/banner/';

    const FALLING_FLOWER = "mpanel/config_animation_effect/image";

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getImageFlower()
    {
        return $this->scopeConfig->getValue(self::FALLING_FLOWER);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFallingImageUrl()
    {
        $Image = $this->getImageFlower();
        $ImageUrl = '';
        if ($Image) {
            $ImageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::UPLOAD_DIR . $Image;
        }
        return $ImageUrl;
    }


}
