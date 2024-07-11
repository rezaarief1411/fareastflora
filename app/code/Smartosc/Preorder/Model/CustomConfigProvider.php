<?php

namespace Smartosc\Preorder\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class CustomConfigProvider
 * @package Smartosc\Preorder\Model
 */
class CustomConfigProvider implements ConfigProviderInterface
{

	/**
	 * @var \Smartosc\Preorder\Helper\Data
	 */
	protected $helper;

	/**
	 * CustomConfigProvider constructor.
	 * @param \Smartosc\Preorder\Helper\Data $helper
	 */
	public function __construct(
		\Smartosc\Preorder\Helper\Data $helper
	){
		$this->helper = $helper;
	}

	/**
	 * @return array
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function getConfig()
	{
		$config = [];
		$preorderDeliveryDate = new \DateTime($this->helper->getFarthestPreorderShippingDay());
		$config['smartosc_preorder_delivery_date'] = $preorderDeliveryDate;

		return $config;
	}
}
