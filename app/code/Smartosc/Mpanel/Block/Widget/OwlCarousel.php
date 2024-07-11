<?php

namespace Smartosc\Mpanel\Block\Widget;

use Magento\Framework\View\Element\Template;

/**
 * Class OwlCarousel
 * @package Smartosc\Mpanel\Block\Widget
 */
class OwlCarousel extends \MGS\Mpanel\Block\Widget\OwlCarousel
{
    /**
     * @var \MGS\Mpanel\Helper\Data
     */
    protected $helper;

    const CFG_SLIDER_1 = 'mpanel/home_slider/slider_1';
    const CFG_SLIDER_2 = 'mpanel/home_slider/slider_2';
    const CFG_SLIDER_3 = 'mpanel/home_slider/slider_3';
    const CFG_SLIDER_4 = 'mpanel/home_slider/slider_4';
    const CFG_SLIDER_5 = 'mpanel/home_slider/slider_5';
    const CFG_EFFECT = 'mpanel/home_slider/effect';
    const CFG_FULL_SCREEN = 'mpanel/home_slider/fullscreen';
    const CFG_AUTO_PLAY = 'mpanel/home_slider/autoplay';
    const CFG_STOP_AUTO = 'mpanel/home_slider/stop_auto';
    const CFG_NAVIGATION = 'mpanel/home_slider/navigation';
    const CFG_LOOP = 'mpanel/home_slider/loop';
    const CFG_RTL = 'mpanel/home_slider/rtl';

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \MGS\Mpanel\Helper\Data $helper,
        Template\Context $context,
        \Magento\Framework\Filesystem\Driver\File $file,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $file, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getImages()
    {
        $images = [];
        $item1 = $this->helper->getStoreConfig(self::CFG_SLIDER_1);
        $item2 = $this->helper->getStoreConfig(self::CFG_SLIDER_2);
        $item3 = $this->helper->getStoreConfig(self::CFG_SLIDER_3);
        $item4 = $this->helper->getStoreConfig(self::CFG_SLIDER_4);
        $item5 = $this->helper->getStoreConfig(self::CFG_SLIDER_5);

        for ($i = 1; $i < 6; $i++) {
            $varName = "item$i";
            if ($$varName) {
                $images[] = $$varName;
            }
        }

        $this->setData('images', implode(',', $images));
        $result = parent::getImages();

        return $result;
    }
}
