<?php

namespace Smartosc\Mpanel\Model\Management;

/**
 * Class Store
 * @package Smartosc\Mpanel\Model\Management
 */
class Store
{
    const STATUS = 'status';

    /**
     * Store constructor.
     */
    public function __construct(
        \MGS\Mpanel\Model\StoreFactory $storeFactory,
        \MGS\Mpanel\Model\ResourceModel\Store\Collection $collection,
        \MGS\Mpanel\Model\ResourceModel\Store $storeResource
    ) {
        $this->storeResource = $storeResource;
        $this->collection = $collection;
        $this->storeFactory = $storeFactory;
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function disableFrontendBuilder()
    {
        $collection = $this->collection->addFieldToFilter(self::STATUS, 1);
        foreach ($collection as $item) {
            $store = $this->storeFactory->create()->load($item->getHomeStoreId());
            $store->setStatus(0);
            $this->storeResource->save($store);
        }
    }
}
