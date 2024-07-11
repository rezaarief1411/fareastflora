<?php

namespace Smartosc\FlowerOverlay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Smartosc\FlowerOverlay\Helper
 */
class Data extends AbstractHelper
{
    const UPLOAD_DIR = 'flower/overlay/';

    const LEFT_IMAGE_FLOWER_OVERLAY = "flower_overlay/image_upload/image_left";

    const RIGHT_IMAGE_FLOWER_OVERLAY = "flower_overlay/image_upload/image_right";

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
    public function getLeftImageFlowerOverlay()
    {
        return $this->scopeConfig->getValue(self::LEFT_IMAGE_FLOWER_OVERLAY);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLeftImageUrl()
    {
        $leftImage = $this->getLeftImageFlowerOverlay();
        $leftImageUrl = '';
        if ($leftImage) {
            $leftImageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::UPLOAD_DIR . $leftImage;
        }
        return $leftImageUrl;
    }

    /**
     * @return mixed
     */
    public function getRightImageFlowerOverlay()
    {
        return $this->scopeConfig->getValue(self::RIGHT_IMAGE_FLOWER_OVERLAY);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRightImageUrl()
    {
        $rightImage = $this->getRightImageFlowerOverlay();
        $rightImageUrl = '';
        if ($rightImage) {
            $rightImageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::UPLOAD_DIR . $rightImage;
        }
        return $rightImageUrl;
    }
}
