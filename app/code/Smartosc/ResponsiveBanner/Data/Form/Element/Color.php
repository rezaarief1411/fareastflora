<?php

namespace Smartosc\ResponsiveBanner\Data\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Color
 * @package Smartosc\ResponsiveBanner\Data\Form\Element
 */
class Color extends AbstractElement
{
    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        $html = parent::getHtml();
        $html .=  '<script>console.log("debug"); require(["jquery", "jquery/colorpicker/js/colorpicker"], function($) {
        	$(document).ready(function () {
                var $el = $("#' . $this->getHtmlId() . '");
                $el.css("backgroundColor", "'.$this->getValue().'");
                // Attach the color picker
                $el.ColorPicker({
                    color: "'.$this->getValue().'",
                    onChange: function (hsb, hex, rgb) {
                        $el.css("backgroundColor", "#" + hex).val("#" + hex);
                    }
                });
            });
        });
       </script>';
        return $html;
    }
}
