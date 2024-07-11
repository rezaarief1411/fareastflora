<?php

namespace Smartosc\ResponsiveBanner\Block;

/**
 * Class Widget
 *
 * This class extends \Mageplaza\BannerSlider\Block\Widget
 */
class Widget extends \Mageplaza\BannerSlider\Block\Widget
{
   /**
    * {@inheritdoc}
    */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Smartosc_ResponsiveBanner::bannerslider.phtml');
    }
}
