<?php

namespace Smartosc\Mpanel\Plugin\CatalogSearch\Block;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

/**
 * Class ResultPlugin
 * @package Smartosc\Mpanel\Plugin\CatalogSearch\Block
 */
class ResultPlugin
{

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * ResultPlugin constructor.
     * @param LayerResolver $layerResolver
     */
    public function __construct(
        LayerResolver $layerResolver
    ) {

        $this->catalogLayer = $layerResolver->get();
    }

    /**
     * @param \Magento\CatalogSearch\Block\Result $subject
     * @param Closure $proceed
     * @return \Magento\CatalogSearch\Block\Result
     */
    public function aroundSetListOrders(
        \Magento\CatalogSearch\Block\Result $subject,
        \Closure $proceed
    ) {
        $category = $this->catalogLayer->getCurrentCategory();
        /* @var $category \Magento\Catalog\Model\Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);

        $subject->getListBlock()->setAvailableOrders(
            $availableOrders
        )->setDefaultDirection(
            'desc'
        )->setDefaultSortBy(
            $category->getDefaultSortBy()
        );

        return $subject;
    }
}
