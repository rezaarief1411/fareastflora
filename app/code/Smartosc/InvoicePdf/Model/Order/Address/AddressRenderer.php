<?php

namespace Smartosc\InvoicePdf\Model\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\Order\Address;

/**
 * Class AddressRenderer
 * @package Smartosc\InvoicePdf\Model\Order\Address
 */
class AddressRenderer extends \Magento\Sales\Model\Order\Address\Renderer
{

    const ADDRESS_BILLING = 'billing';
    const ADDRESS_SHIPPING = 'shipping';

    private function _getBuilding(Address $address)
    {
        $res = '';

        if ($address->getAddressType() == self::ADDRESS_BILLING) {
            $res = $address->getOrder()->getBillingBuilding();
        } elseif ($address->getAddressType() == self::ADDRESS_SHIPPING) {
            $res = $address->getOrder()->getShippingBuilding();
        }

        return $res;
    }

    /**
     * @param Address $address
     * @return string
     */
    private function _getFloor($address)
    {
        $res = '';

        if ($address->getAddressType() == self::ADDRESS_BILLING) {
            $res = $address->getOrder()->getBillingFloor();
        } elseif ($address->getAddressType() == self::ADDRESS_SHIPPING) {
            $res = $address->getOrder()->getShippingFloor();
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function format(Address $address, $type)
    {
        $trans = [
            'mr' => 'Mr.',
            'mrs' => 'Mrs.',
            'ms' => 'Ms.',
            'dr' => 'Dr.',
            'mdm' => 'Mdm.',
        ];

        $this->addressConfig->setStore($address->getOrder()->getStoreId());
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);

        $prefixKey = $address->getData('prefix');

        if (isset($trans[$prefixKey])) {
            $address->setData('prefix', $trans[$prefixKey]);
        }

        if ($this->_getBuilding($address)) {
            $address->setData('building', $this->_getBuilding($address));
        }

        if ($this->_getFloor($address)) {
            $address->setData('floor', $this->_getFloor($address));
        }

        return $formatType->getRenderer()->renderArray($address->getData());
    }
}
