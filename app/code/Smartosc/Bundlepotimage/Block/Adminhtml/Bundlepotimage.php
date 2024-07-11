<?php

namespace Smartosc\Bundlepotimage\Block\Adminhtml;

/**
 * Class Bundlepotimage
 * @package Smartosc\Bundlepotimage\Block\Adminhtml
 */
class Bundlepotimage extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_bundlepotimage';
        $this->_blockGroup = 'Smartosc_Bundlepotimage';
        $this->_headerText = __('Manage Preview Images');
        $this->_addButtonLabel = __('Add Image');
        parent::_construct();
    }
}
