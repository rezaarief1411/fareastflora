<?php

namespace Smartosc\Checkout\Model\Form;

/**
 * Class Register
 * @package Smartosc\Checkout\Model\Form
 */
class Register extends AbstractForm
{
    const PREFIX_SHOW = 'customer/address/prefix_show';

    const PREFIX_OPTIONS = 'customer/address/prefix_options';

    /**
     * @return bool
     */
    protected function isPrefixRequired()
    {
        $customerAddressPrefixShowValue = false;
        $config = $this->helper->getStoreConfig(self::PREFIX_SHOW);

        if ($config && $config == 'req')
            $customerAddressPrefixShowValue = true;

        return $customerAddressPrefixShowValue;
    }

    /**
     * @return array
     */
    protected function getPrefixOptions()
    {
        $config = $this->helper->getStoreConfig(self::PREFIX_OPTIONS); // result: ;Mr.;Mrs.;Mdm.;Ms.;Dr.

        $customerAddressPrefixOptions = explode(';', $config);
        foreach ($customerAddressPrefixOptions as $prefix) {
            $customerAddressPrefixOptions[] = [
                'value' => $prefix,
                'label' => $prefix
            ];
        }

        return $customerAddressPrefixOptions;
    }

    /**
     * @param string $field
     * @return array
     */
    public function getRendererArray($field = '')
    {
        $result = [];
        switch ($field) {
            case 'prefix':
                $result = $this->addFieldToLayout('prefix', [
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config' => [
                        'customScope' => 'customer-email.prefix',
                        'elementTmpl' => 'ui/form/element/select'
                    ],
                    'template' => 'ui/form/field',
                    'dataScope' => 'customer-email.prefix',
                    'label' => null,
                    'validation' => [
                        'required-entry' => $this->isPrefixRequired()
                    ],
                    'options' => $this->getPrefixOptions()

                ]);
                break;

            default:
        }

        return $result;
    }
}
