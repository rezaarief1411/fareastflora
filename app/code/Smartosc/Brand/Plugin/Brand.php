<?php

namespace Smartosc\Brand\Plugin;

/**
 * Class Brand
 * @package Smartosc\Brand\Plugin
 */
class Brand
{
    protected $request;

    /**
     * Brand constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \MGS\Brand\Model\Layer\Brand $brand
     * @param $productCollection
     * @return mixed
     */
    public function afterGetProductCollection(\MGS\Brand\Model\Layer\Brand $brand, $productCollection)
    {
        $sort = $this->request->getParam('product_list_order');
        if ($sort) {
            $arr = explode('_', $sort);
            if (in_array($sort, ['created_at_asc', 'created_at_desc'])) {
                if ($sort == 'created_at_asc') {
                    $productCollection->addAttributeToSort('created_at', 'asc');
                } else {
                    $productCollection->addAttributeToSort('created_at', 'desc');
                }
            } elseif (sizeof($arr) == 2) {
                //columnorder_dir
                $order = reset($arr);
                $dir = end($arr);
                $productCollection->addAttributeToSort($order, $dir);
            }
        }
        return $productCollection;
    }
}
