<?php

namespace Smartosc\CustomBundleProduct\Block\Product\Renderer\FrontPage;

/**
 * Class BundleRenderer
 * @package Smartosc\CustomBundleProduct\Block\Product\Renderer\FrontPage
 */
class BundleRenderer extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle implements \Smartosc\CustomBundleProduct\Block\Product\Renderer\BundleRendererInterface
{
    use \Smartosc\CustomBundleProduct\Block\Product\Renderer\BunderRenderer;

    /**
     * @var string
     */
    protected $_template = 'Smartosc_CustomBundleProduct::product/frontpage/renderer.phtml';

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_optionViews = [
            'radio' => 'Smartosc_CustomBundleProduct::product/frontpage/product/view/option/radio.phtml',
            'checkbox' => 'Smartosc_CustomBundleProduct::product/frontpage/product/view/option/checkbox.phtml'
        ];
        parent::_construct();
    }

    /**
     * {@inheritDoc}
     */
    public function getJsonConfig()
    {
        /** @var \Magento\Bundle\Model\Option[] $optionsArray */
        $optionsArray = $this->getOptions();
        $options = [];
        $currentProduct = $this->getProduct();

        $defaultValues = [];
        $preConfiguredFlag = $currentProduct->hasPreconfiguredValues();
        /** @var \Magento\Framework\DataObject|null $preConfiguredValues */
        $preConfiguredValues = $preConfiguredFlag ? $currentProduct->getPreconfiguredValues() : null;

        $position = 0;
        foreach ($optionsArray as $optionItem) {
            /* @var $optionItem \Magento\Bundle\Model\Option */
            if (!$optionItem->getSelections()) {
                continue;
            }

            $optionId = $optionItem->getId();
            $options[$optionId] = $this->getOptionItemData($optionItem, $currentProduct, $position);

            $this->optionsPosition[$position] = $optionId;

            // Add attribute default value (if set)
            if ($preConfiguredFlag) {
                $configValue = $preConfiguredValues->getData('bundle_option/' . $optionId);
                if ($configValue) {
                    $defaultValues[$optionId] = $configValue;
                    $configQty = $preConfiguredValues->getData('bundle_option_qty/' . $optionId);
                    if ($configQty) {
                        $options[$optionId]['selections'][$configValue]['qty'] = $configQty;
                    }
                }
                $options = $this->processOptions($optionId, $options, $preConfiguredValues);
            }
            $position++;
        }
        $config = $this->getConfigData($currentProduct, $options);

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions($stripSelection = false)
    {
        $this->resetProductOptions();
        return parent::getOptions($stripSelection);
    }
}
