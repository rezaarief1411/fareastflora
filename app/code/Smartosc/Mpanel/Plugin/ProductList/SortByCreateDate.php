<?php

namespace Smartosc\Mpanel\Plugin\ProductList;

/**
 * Class SortByCreateDate
 * @package Smartosc\Mpanel\Plugin\ProductList
 */
class SortByCreateDate
{
    /**
     * @param \Magento\Catalog\Model\Config $instance
     * @param array $result
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $instance, $result)
    {
        if (!isset($result['created_at'])) {
            $result['created_at'] = __('Date');
        }
        asort($result);

        return $result;
    }
}
