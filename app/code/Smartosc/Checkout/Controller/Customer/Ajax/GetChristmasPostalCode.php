<?php

namespace Smartosc\Checkout\Controller\Customer\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class GetChristmasPostalCode
 * @package Smartosc\Checkout\Controller\Customer\Ajax
 */
class GetChristmasPostalCode extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    const CONFIG_POSTAL_CODE_CHRISTMAS = 'mpanel/sentosa/postal_code';

    /**
     * GetChristmasPostalCode constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\ConfigProviderInterface[] $configProviders
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig
    ) {
         $this->scopeConfig = $scopeConfig;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $httpBadRequestCode = 400;
        $response = [
            'errors' => false,
            'message' => ''
        ];

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        if ($this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if ($checkoutConfig = $this->scopeConfig->getValue(self::CONFIG_POSTAL_CODE_CHRISTMAS)) {

            $response = [
                'errors' => false,
                'message' =>  'ok',
                'data' => explode(',', $checkoutConfig)
            ];
        }

        return $resultJson->setData($response);
    }
}
