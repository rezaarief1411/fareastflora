<?php

namespace Smartosc\Checkout\Model\Config\Backend;

/**
 * Class DatePickerList
 * @package Smartosc\Checkout\Model\Config\Backend
 */
class DatePickerList extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @return DatePickerList
     */
    public function beforeSave()
    {
        $value = [];
        $values = $this->getValue();
        foreach ((array)$values as $key => $data) {
            if ($key == '__empty') {
                continue;
            }
            if (!isset($data['date'])) {
                continue;
            }
            try {
                $date = \DateTime::createFromFormat('d-m-Y', $data['date']);
                $value[$key] = [
                    'date' => $date->format('d-m-Y')
                ];
            } catch (\Exception $e) {
                throwException($e);
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
