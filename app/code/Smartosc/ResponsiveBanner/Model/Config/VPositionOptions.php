<?php

namespace Smartosc\ResponsiveBanner\Model\Config;

/**
 * Class VPositionOptions
 *
 * Provide options for the vertical position of banner text
 */
class VPositionOptions implements \Magento\Framework\Option\ArrayInterface
{
    const TOP_VALUE = 1;

    const MIDDLE_VALUE = 2;
    
    const BOTTOM_VALUE = 3;
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TOP_VALUE, 'label' => __('Top')],
            ['value' => self::MIDDLE_VALUE, 'label' => __('Middle')],
            ['value' => self::BOTTOM_VALUE, 'label' => __('Bottom')]
        ];
    }
}
