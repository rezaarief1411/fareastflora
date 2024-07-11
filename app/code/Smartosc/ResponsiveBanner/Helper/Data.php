<?php

namespace Smartosc\ResponsiveBanner\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Data
 * @package Smartosc\ResponsiveBanner\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Mageplaza\BannerSlider\Helper\Data
     */
    protected $sliderHelper;

    const BLOG_SHORT_DESCRIPTION = 'blog/general_settings/short_description';

    /**
     * Data constructor.
     * @param Context $context
     * @param \Mageplaza\BannerSlider\Helper\Data $sliderHelper
     */
    public function __construct(
        Context $context,
        \Mageplaza\BannerSlider\Helper\Data $sliderHelper
    ) {
        $this->sliderHelper = $sliderHelper;
        parent::__construct($context);
    }

    /**
     * @return DataObject
     * @throws NoSuchEntityException
     */
    public function getSlidersForBlog()
    {
        $activeSliders = $this->sliderHelper->getActiveSliders();
        $sliderForBlog = $activeSliders->addFieldToFilter('enable_blog', 1)
            ->setOrder('priority', 'ASC')
            ->getFirstItem();
        return $sliderForBlog;
    }

    /**
     * @return mixed|string
     */
    public function getShortDescription()
    {
        $shortDescription = '';
        if ($this->scopeConfig->getValue(self::BLOG_SHORT_DESCRIPTION)) {
            $shortDescription = $this->scopeConfig->getValue(self::BLOG_SHORT_DESCRIPTION);
        }
        return $shortDescription;
    }
}
