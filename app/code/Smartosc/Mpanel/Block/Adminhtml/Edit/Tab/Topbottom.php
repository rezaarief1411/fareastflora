<?php

namespace Smartosc\Mpanel\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Widget\Form;

/**
 * Class Topbottom
 * @package Smartosc\Mpanel\Block\Adminhtml\Edit\Tab
 */
class Topbottom extends \MGS\Mmegamenu\Block\Adminhtml\Edit\Tab\Topbottom
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('mmegamenu_mmegamenu');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('mmegamenu_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Static Contents')]);

        $fieldset->addField(
            'top_content',
            'editor',
            [
                'name' => 'top_content',
                'label' => __('Top Content'),
                'title' => __('Top Content'),
                'style' => 'height:12em',
                'config' => $this->_wysiwygConfig->getConfig()
            ]
        );


        $form->setValues($model->getData());
        $this->setForm($form);

        return Form::_prepareForm();
    }
}
