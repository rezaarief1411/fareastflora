<?php

namespace Smartosc\ResponsiveBanner\Model\Config;

/**
 * Class PositionOptions
 *
 * Provide options for the horizontal position of banner text
 */
class PositionOptions implements \Magento\Framework\Option\ArrayInterface
{
    const LEFT_VALUE = 1;

    const RIGHT_VALUE = 2;
    
    const CENTER_VALUE = 3;
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LEFT_VALUE, 'label' => __('Left')],
            ['value' => self::RIGHT_VALUE, 'label' => __('Right')],
            ['value' => self::CENTER_VALUE, 'label' => __('Center')]
        ];
    }
}
