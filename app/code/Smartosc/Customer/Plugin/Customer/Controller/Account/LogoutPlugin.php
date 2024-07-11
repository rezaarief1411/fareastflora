<?php

namespace Smartosc\Customer\Plugin\Customer\Controller\Account;

use Magento\Customer\Controller\Account\Logout;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class LogoutPlugin
 * @package Smartosc\Customer\Plugin\Customer\Controller\Account
 */
class LogoutPlugin
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * LogoutPlugin constructor.
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
    }
    /**
     * @param Logout $subject
     * @param Redirect $result
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(Logout $subject, Redirect $result): Redirect
    {
        $this->messageManager->addSuccessMessage(__("You've Been Logged Out"));
        $result->setPath('/');
        return $result;
    }
}
