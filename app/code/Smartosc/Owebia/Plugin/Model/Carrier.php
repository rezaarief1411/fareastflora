<?php

namespace Smartosc\Owebia\Plugin\Model;

class Carrier
{
    /**
     * @param RateRequest|null $request
     * @return mixed|null
     */
    public function afterGetConfig(\Owebia\AdvancedShipping\Model\Carrier $subject, $result, $request)
    {
        if (is_array($result) && $result !== null) {
            $result = $this->getMaxShippingPrice($result);
        }
        return $result;
    }

    public function getMaxShippingPrice($config)
    {
        $result = $maxPriceItem = [];
        if (sizeof($config) == 1) {
            return $config;
        }
        $maxPrice = 0;
        foreach ($config as $index => $item) {
            if ($item->enabled) {
                if ($item->price > $maxPrice) {
                    $maxPriceItem = [
                        $index => $item
                    ];
                    $maxPrice = $item->price;
                }
            } else {
                $result[$index] = $item;
            }
        }
        if (!empty($maxPriceItem)){
            return array_merge($maxPriceItem, $result);
        }
        return $result;
    }
}
