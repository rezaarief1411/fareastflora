<?php

namespace Smartosc\CustomBundleProduct\Model\BundleProduct;

/**
 * Interface ReportServiceInterface
 * @package Smartosc\CustomBundleProduct\Model\BundleProduct
 */
interface ReportServiceInterface
{
    const PRICE = 'price';
    const NAME = 'name';
    const ITEM_CODE = 'item_code';
    const IMAGE = 'image';

    /**
     * @return string
     */
    public function getPrice();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getItemCode();

    /**
     * @return string
     */
    public function getImage();
}