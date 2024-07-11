<?php
namespace Smartosc\CustomBundleProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetItemHasRepotNotRequire implements ObserverInterface
{
    /**
     * @var \Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService
     */
    protected $reportService;

    public function __construct(\Smartosc\CustomBundleProduct\Model\BundleProduct\ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $quoteItem->setIsRepotNotRequire(false);
            if($this->getProductRepotNotRequire($quoteItem)) {
                $quoteItem->setIsRepotNotRequire(true);
            }
        }
    }
    /**
     * @param \Magento\Quote\Model\Quote\Item $parentItem
     * @param $childItemIds
     * @return bool
     */
    protected function getProductRepotNotRequire($parentItem) {
        $childItemProductIds = $productChildIds = [];
        $getProductIdRepot = $this->reportService->getReportServiceProduct();
        if(!$getProductIdRepot || !$this->reportService->isEnableFeature()) {
            return false;
        }
        foreach ($parentItem->getChildren() as $itemChild) {
            $childItemProductIds[] = $itemChild->getProduct()->getId();
        }
        $productBundle = $parentItem->getProduct();
        $typeInstance = $parentItem->getProduct()->getTypeInstance();
        $requiredChildrenIds = $typeInstance->getChildrenIds($productBundle->getId(), false);
        foreach ($requiredChildrenIds as $arrChildData) {
            foreach ($arrChildData as $entityId) {
                $productChildIds[] = $entityId;
            }
        }
        if(!in_array($getProductIdRepot, $childItemProductIds) && in_array($getProductIdRepot, $productChildIds)) {
            return true;
        }
        return false;
    }
}