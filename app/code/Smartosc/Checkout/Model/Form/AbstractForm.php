<?php

namespace Smartosc\Checkout\Model\Form;

/**
 * Class Checkout
 * @package Smartosc\Checkout\Model\Form
 */
abstract class AbstractForm
{
    /**
     * @var \Smartosc\Checkout\Helper\Data
     */
    protected $helper;

    /**
     * Checkout constructor.
     * @param \Smartosc\Checkout\Helper\Data $helper
     */
    public function __construct(
        \Smartosc\Checkout\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param string $customAttributeCode
     * @param array $customField
     * @return array
     */
    protected function addFieldToLayout($customAttributeCode = 'custom_field', $customField = [])
    {
        return array_merge([
            'component' => 'Magento_Ui/js/form/element/date',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/date'
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
            'label' => 'Custom Attribute',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'validation' => [
                'required-entry' => true
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'value' => ''
        ], $customField);
    }
}
