<?php

namespace Smartosc\CustomBundleProduct\Block\Product\Renderer;

/**
 * Interface BundleRendererInterface
 * @package Smartosc\CustomBundleProduct\Block\Product\Renderer
 */
interface BundleRendererInterface
{
    /**
     * @param \Magento\Bundle\Model\Option $option
     * @return string
     */
    public function getProductOptionsHtml(\Magento\Bundle\Model\Option $option);

    /**
     * @return $this
     */
    public function resetProductOptions();
}
