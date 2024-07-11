<?php
namespace Smartosc\CustomBundleProduct\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Smartosc\CustomBundleProduct\Helper\Data as Helper;

/**
 * Class CustomConfigProvider
 *
 * @package Smartosc\CustomBundleProduct\Model
 */
class CustomConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Smartosc\CustomBundleProduct\Helper\Data
     */
    protected $helper;

    /**
     * CustomConfigProvider constructor.
     *
     * @param \Smartosc\CustomBundleProduct\Helper\Data $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $reportServiceProductId = $this->helper->getReportServiceProductId();
        if ($reportServiceProductId) {
            $config['reportServiceProductId'] = $reportServiceProductId;
        }

        return $config;
    }
}
