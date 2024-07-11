<?php

namespace Smartosc\Checkout\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class SpecialDate
 * @package Smartosc\Checkout\Helper
 */
class SpecialDate extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     *
     */
    const SPECIAL_DISABLE_DATE = "smartosc_special_date/special/disable_date";

    /**
     * SpecialDate constructor.
     * @param Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(Context $context, \Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return false|string[]
     */
    public function getSpecialDisableDate()
    {
        $result = [];
        $specialDate = $this->scopeConfig->getValue(self::SPECIAL_DISABLE_DATE);
        if ($specialDate !== "[]") {
            $specialDateArr = $this->jsonHelper->jsonDecode($specialDate);
            if (!empty($specialDateArr)) {
                foreach ($specialDateArr as $key => $value) {
                    $result[] = $value['date'];
                }
            }
        }
        return $result;
    }
}
