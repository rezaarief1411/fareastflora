<?php

namespace Smartosc\Checkout\Helper\Product;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Smartosc\Checkout\Helper\Product
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;

        parent::__construct($context);
    }

    /**
     * @param $id
     * @return string
     */
    public function getProductImageUrl($id)
    {
        $product = $this->productFactory->create()->load($id);
        $url = $this->imageHelper->init($product, 'product_page_image_medium')->getUrl();

        return $url;
    }

    /**
     * @param $id
     * @return array
     */
    public function getProductImageWithSize($id)
    {
        $product = $this->productFactory->create()->load($id);
        $image = $this->imageHelper->init($product, 'product_page_image_medium');


        return [
            'url' => $image->getUrl(),
            //@todo get small width/height here
            //'width' => $image->getWidth(),
            //'height' => $image->getHeight()
        ];
    }

    /**
     * @param int $optionId
     * @return string
     */
    public function getAttributeSwatchHasCode($optionId)
    {
        if ($optionId == null) {
            return;
        }
        $hashcodeData = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);

        return $hashcodeData[$optionId]['value'];
    }

    /**
     * @param int $productId
     * @param string
     * @return null|int
     */
    public function getAttributeSwatchOptionId($productId, $swatchValue)
    {
        $optionId = null;
        $product = $this->productFactory->create()->load($productId);
        $attr = $product->getResource()->getAttribute('color');
        if ($attr->usesSource()) {
            $optionId = $attr->getSource()->getOptionId($swatchValue);
        }

        return $optionId;
    }
}
