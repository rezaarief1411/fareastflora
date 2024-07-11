<?php

namespace Smartosc\Customer\Plugin\Customer\Block\Account;

use Magento\Customer\Block\Account\RegisterLink;

/**
 * Class CustomRegisterLink
 * @package Smartosc\Customer\Plugin\Customer\Block\Account
 */
class CustomRegisterLink
{
    /**
     * @param RegisterLink $subject
     * @param string $result
     * @return string
     */
    public function afterGetHref(RegisterLink $subject, $result)
    {
        return $subject->getUrl('customer/account/login');
    }
}
