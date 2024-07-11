<?php 
namespace Fef\CustomShipping\Plugin;

class CheckoutLayoutProcessor
{
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $result)
    {

        $deliveryStepUiComponent = &$result['components']['checkout']['children']['steps']['children']['delivery-step'];

        $deliverySlotAttributes = $this->getDeliverySlotAttributes();
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_slot'] = $this->getAdditionalDeliveryComponent($deliverySlotAttributes,'delivery-slot');
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_slot']['children']['delivery_slot']['placeholder'] = __('Please select timeslot');
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_slot']['sortOrder'] = 5;
        
        $deliveryStairsAttributes = $this->getDeliveryStairsAttributes();
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_stairs'] = $this->getAdditionalDeliveryComponent($deliveryStairsAttributes,'delivery-stairs');
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_stairs']['children']['delivery_stairs']['placeholder'] = __('Enter building level');
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_stairs']['sortOrder'] = 6;

        return $result;
    }

    /**
     * @return array
     */
    private function getDeliverySlotAttributes()
    {

        $elements['delivery_slot']=$this->addFieldToLayout('delivery_slot', [
            'dataType' => 'select',
            'formElement' => 'select',
            'sortOrder' => 25,
            'validation' => [
                'required-entry' => true
            ],
            "options" => [
                ['value' => '', 'label' => __('Please select timeslot')]
                // ['value' => '08:00 - 14:00', 'label' => __('08:00 - 14:00')],
                // ['value' => '14:00 - 18:00', 'label' => __('14:00 - 18:00')]
            ]
        ]);


        return $elements;
    }

     /**
     * @return array
     */
    private function getDeliveryStairsAttributes()
    {
        
        $elements['delivery_stairs']=$this->addFieldToLayout('delivery_stairs', [
            'dataType' => 'text',
            'formElement' => 'input',
            'label' => "Enter a building level",
            'additionalClasses' => 'delivery_stairs',
            'id' => 'delivery_stairs',
            'sortOrder' => 26,
            'validation' => [
                'required-entry' => false
            ],
            'value' => ''
        ]);


        return $elements;
    }

    /**
     * @param $elements
     * @return array
     */
    protected function getAdditionalDeliveryComponent($elements,$elCode)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();   
        $merge = $objectManager->get('\Magento\Checkout\Block\Checkout\AttributeMerger');

        $components = [
            'component' => 'uiComponent',
            'displayArea' => $elCode,
            'children' => $merge->merge(
                $elements,
                'checkoutProvider',
                $elCode,
                []
            )
        ];

        return $components;
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
            'label' => '',
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