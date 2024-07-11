<?php

namespace Smartosc\Bundlepotimage\Block\Adminhtml\Edit;

/**
 * Class Form
 * @package Smartosc\Bundlepotimage\Block\Adminhtml\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bundlepotimage__form');
        $this->setTitle(__('Bundle&Pot Images Information'));
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('bundlepotimage_bundlepotimage');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $fieldset = $form->addFieldset('add_bundlepotimage_form', ['legend' => __('Preview image Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'identifier',
            'text',
            [
                'label' => __('Identifier'),
                'name' => 'identifier',
                'required' => true,
                'class' => 'validate-xml-identifier'
            ]
        );

        $fieldset->addField('bundle_sku', 'text', [
            'label' => __('Bundle Sku'),
            'name' => 'bundle_sku',
            'required' => true
        ]);

        $fieldset->addField('pot_sku', 'text', [
            'label' => __('Pot Sku'),
            'name' => 'pot_sku',
            'required' => true
        ]);

        if ($this->getRequest()->getParam('id')) {
            $fieldset->addField(
                'bundle_pot_image',
                'file',
                [
                    'label' => __('Preview Image'),
                    'name' => 'bundle_pot_image',
                    'required' => false
                ]
            );
        } else {
            $fieldset->addField(
                'bundle_pot_image',
                'file',
                [
                    'label' => __('Preview Image'),
                    'name' => 'bundle_pot_image',
                    'required' => true
                ]
            );
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
