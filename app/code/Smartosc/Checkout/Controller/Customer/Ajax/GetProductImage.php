<?php

namespace Smartosc\Checkout\Controller\Customer\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class GetProductImage
 * @package Smartosc\Checkout\Controller\Customer\Ajax
 */
class GetProductImage extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item
     */
    protected $itemResourceModel;
    /**
     * @var \Smartosc\Checkout\Helper\Product\Data
     */
    protected $productDataHelper;

    /**
     * GetProductImage constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel
     * @param \Smartosc\Checkout\Helper\Product\Data $productDataHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        \Smartosc\Checkout\Helper\Product\Data $productDataHelper
    ) {
        $this->productDataHelper = $productDataHelper;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
         $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

        $response = [
            'errors' => false,
            'message' => ''
        ];

        $quoteItemId = $this->getRequest()->getParam('quoteItemId');
        try {
            if ($quoteItemId) {
                $quoteItemModel = $this->quoteItemFactory->create();

                /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $quoteItem */
                $quoteItem = $quoteItemModel->load($quoteItemId);
                $productId = $quoteItem->getData('product_id');

                $response = [
                   'errors' => false,
                   'imageUrl' =>  $this->productDataHelper->getProductImageUrl($productId),
                   'message' => __('Get product image successfully!')
                ];
            } else {
                throw new LocalizedException(__("Something wrong with quoteItemId. Please try again"));
            }
        } catch (\Exception $exception) {
            $response = [
               'errors' => true,
               'message' => $exception->getMessage()
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
