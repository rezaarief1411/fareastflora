<?php

namespace Smartosc\CustomLinked\Ui\DataProvider\Product\Related;

/**
 * Class CustomLinkedDataProvider
 *
 * Custom DataProvider
 */
class CustomLinkedDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider
{
    /**
     * @return string
     */
    protected function getLinkType()
    {
        return 'add_on';
    }
}
