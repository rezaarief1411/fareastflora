<?php
namespace Smartosc\Catalog\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\App\RequestInterface;
/**
 * Class FilterHelper
 * @package Smartosc\Catalog\Helper
 */
class FilterHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * CartItemHelper constructor.
     * @param Context $context
     * @param Data $jsonHelper
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        RequestInterface $request
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getColorId()
    {
        $color = $this->request->getParam('color');
        if (!$color) {
            return [];
        }

        return explode( ',', $color);
    }
}
