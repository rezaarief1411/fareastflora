<?php

namespace Smartosc\Catalog\Plugin\Model;

class Product
{
    const ONE_DATE_TO_STRING = 86400;

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param $result
     * @return false|string
     */
    public function afterGetSpecialToDate(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($result) {
            return date('Y-m-d H:i:s', strtotime($result) - self::ONE_DATE_TO_STRING);
        }
    }
}
