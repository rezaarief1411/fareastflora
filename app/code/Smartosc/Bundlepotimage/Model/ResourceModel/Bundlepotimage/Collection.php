<?php

namespace Smartosc\Bundlepotimage\Model\ResourceModel\Bundlepotimage;

/**
 * Bundlepotimage resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(
            \Smartosc\Bundlepotimage\Model\Bundlepotimage::class,
            \Smartosc\Bundlepotimage\Model\ResourceModel\Bundlepotimage::class
        );
    }
}
