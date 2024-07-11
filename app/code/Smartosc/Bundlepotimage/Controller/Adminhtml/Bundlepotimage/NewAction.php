<?php

namespace Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage;

/**
 * Class NewAction
 * @package Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
 */
class NewAction extends \Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
{
    /**
     * Create new customer action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }
}
