<?php

namespace Smartosc\Checkout\Helper\Order;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Data
 * @package Smartosc\Checkout\Helper\Order
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param string $date
     * @param string $format
     * @return string
     */
    public function getDate($date, $format = 'd-M-Y') {
        try {
            if ($date === 'NaN-NaN-NaN') {
                throw new LocalizedException(__('Date string is not valid'));
            }

            $date = new \DateTime($date);
            return $date->format($format);
        } catch (\Exception $exception) {
            return '';
        }
    }
}
