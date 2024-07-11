<?php
namespace Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage;

use Magento\Backend\App\Action;

/**
 * Class Index
 * @package Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
 */
class Index extends \Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Preview Images'));
        $this->_view->renderLayout();
    }
}
