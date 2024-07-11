<?php

namespace Smartosc\CustomBundleProduct\Block\Order\Email\Items\Order;

/**
 * Class PseudoItem
 * @package Smartosc\CustomBundleProduct\Block\Order\Email\Items\Order
 */
class PseudoItem extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $repotService;

    /**
     * @var string
     */
    protected $_template = 'Smartosc_CustomBundleProduct::email/items/order/pseudoItem.phtml';

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    private $_item;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * PseudoItem constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        array $data = []
    ) {
        $this->repotService = $reportService;
        $this->orderItemFactory = $itemFactory;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $orderItem = $this->orderItemFactory->create()->addData([
            'name' => $this->repotService->getName(),
            'sku' => $this->repotService->getItemCode()
        ]);
        $this->setItem($orderItem);
        parent::_construct();
    }

    /**
     * @return  \Magento\Sales\Model\Order\Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * @param  \Magento\Sales\Model\Order\Item $item
     * @return  $this
     */
    public function setItem($item)
    {
        $this->_item = $item;
        return $this;
    }
}
