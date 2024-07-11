<?php

/**
 * Sitemap grid link column renderer
 *
 */
namespace Smartosc\Bundlepotimage\Block\Adminhtml\Grid\Renderer;

/**
 * Class Preview
 * @package Smartosc\Bundlepotimage\Block\Adminhtml\Grid\Renderer
 */
class Preview extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<div style="max-width:450px"><div class="bundlepotimage'.$this->getCustomClass($row).'">';
        $html .= '<a><img alt="" src="'.$this->getBundlepotimageUrl($row).'" class="img-responsive" /></a>';

        if (($row->getContent() != '') || ($row->getButton() != '')) {
            $html .= '<div class="text '.$row->getTextAlign().'">';

            if ($row->getContent() != '') {
                $html .= '<div class="bundlepotimage-text">'.$row->getContent().'</div>';
            }
            if ($row->getButton() != '') {
                $html .= '<span class="bundlepotimage-button"><button class="btn btn-default btn-bundlepotimage">'.$row->getButton().'</button></span>';
            }
            $html .= '</div>';
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * @param $bundlepotimage
     *
     * @return string
     */
    public function getBundlepotimageUrl($bundlepotimage)
    {
        $bundlepotimageUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'bundlepotimage'.$bundlepotimage->getBundlePotImage();
        return $bundlepotimageUrl;
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
