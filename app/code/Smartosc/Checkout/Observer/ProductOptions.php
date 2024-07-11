<?php
namespace Smartosc\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\OptionFactory;

/**
 * Class Productoptions
 * @package Smartosc\Checkout\Observer
 */
class ProductOptions implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_options;
    /**
     * @var OptionFactory
     */
    protected $productOptionFactory;

    /**
     * Productoptions constructor.
     * @param \Magento\Catalog\Model\Product\Option $options
     * @param OptionFactory $productOptionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $options,
        OptionFactory $productOptionFactory
    ) {
        $this->_options = $options;
        $this->productOptionFactory = $productOptionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        //check if the custom option exists
        foreach ($product->getOptions() as $option) {
            if ($option->getTitle() == 'Disposal Collection Date' && $option->getType()=='date') {
                $option->delete();
                break;
            }
        }
    }
}
