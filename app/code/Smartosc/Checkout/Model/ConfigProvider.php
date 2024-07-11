<?php

namespace Smartosc\Checkout\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutInterface;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 * @package Smartosc\Checkout\Model
 */
class ConfigProvider implements ConfigProviderInterface
{

    const BLOCK_PROMO = 'promo_info_on_checkout';

    /** @var LayoutInterface  */
    protected $_layout;

    /**
     * @var string
     */
    protected $cmsBlock;

    /**
     * @var \MGS\StoreLocator\Model\ResourceModel\Store\Collection
     */
    protected $storeLocatorCollection;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var SmartGift\Products
     */
    protected $gift;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $configHelper;

    /**
     * @var \Smartosc\Checkout\Helper\SpecialDate
     */
    protected $specialDate;

    /**
     * @var \Smartosc\Checkout\Model\Quote\CustomPricingFactory
     */
    protected $customPricingFactory;

    /**
     * ConfigProvider constructor.
     * @param LayoutInterface $_layout
     * @param \MGS\StoreLocator\Model\ResourceModel\Store\Collection $storeLocatorCollection
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Helper\Data $taxHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Smartosc\Checkout\Helper\SpecialDate $specialDate
     */
    public function __construct(
        LayoutInterface $_layout,
        \MGS\StoreLocator\Model\ResourceModel\Store\Collection $storeLocatorCollection,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Smartosc\Checkout\Model\Quote\CustomPricingFactory $customPricingFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Data $taxHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Smartosc\Checkout\Model\SmartGift\Products $gift,
        \Magento\Framework\App\Config $configHelper,
        \Smartosc\Checkout\Helper\SpecialDate $specialDate
    ) {
        $this->configHelper = $configHelper;
        $this->serializer = $serializer;
        $this->storeLocatorCollection = $storeLocatorCollection;
        $this->_layout = $_layout;
        $this->gift = $gift;
        $this->taxHelper = $taxHelper;
        $this->cart = $cart;
        $this->specialDate = $specialDate;
        $this->customPricingFactory = $customPricingFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $output['cms_block'] = $this->constructBlock(self::BLOCK_PROMO);
        $output['storesList'] = $this->serializer->serialize($this->getAllStores());
        $output['base_original_subtotal'] = $this->getOldPrice();
        $output['total_saving'] = $this->getTotalSaving();
        $gifts = $this->gift->getList();
        $output['gift'] = json_encode($gifts);
        $output['delivery_date_rule_setting'] = $this->getPostalCodeAndDateRestrict();
        $output['special_disable_date'] = $this->specialDate->getSpecialDisableDate();
        $output['pickup_note_limit'] = $this->configHelper->getValue('mpanel/note_settings/pickup_note_limit');
        $output['delivery_note_limit'] = $this->configHelper->getValue('mpanel/note_settings/delivery_note_limit');

        return $output;
    }

    // postal code settings which special delivery date
    // if customer enter a postal code in this list, he would only able to choose delivery in a restricted range
    /**
     * @throws LocalizedException
     */
    protected function getPostalCodeAndDateRestrict()
    {
        $configPostalCode = 'mpanel/sentosa/postal_code';
        $configPostalCode = $this->configHelper->getValue($configPostalCode);

        $arrayOfPostal = explode(',', $configPostalCode);

        //try {
        //    $this->validPostalCode($arrayOfPostal);
        //} catch (LocalizedException $e) {
        //    throw  new LocalizedException(__('Postal code string is not valid! Please follow this pattern: 12345,3333,5555'));
        //}

        return [
            'postal_code' => $arrayOfPostal,
        ];
    }

    /**
     * @return false|float|int
     */
    public function getOldPrice()
    {
        return $this->customPricingFactory->create()
                                          ->setCart($this->cart)
                                          ->getBaseOriginalPrice();
    }

    /**
     * @return false|float|int
     */
    public function getTotalSaving()
    {

        return $this->customPricingFactory->create()
                                          ->setCart($this->cart)
                                          ->getTotalSaving();
    }
    /**
     * @return array
     */
    protected function getAllStores()
    {
        $items = [];
        $allStores = $this->storeLocatorCollection;
        foreach ($allStores->getItems() as $store) {
            $items[] = $store->getData();
        }
        return $items;
    }

    /**
     * @param string $blockId
     * @return string
     */
    public function constructBlock($blockId)
    {
        return $this->_layout->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId)->toHtml();
    }

    /**
     * @param $arrayOfPostal
     * @return bool
     * @throws LocalizedException
     */
    private function validPostalCode($arrayOfPostal)
    {
        foreach ($arrayOfPostal as $code) {
            if (!filter_var($code, FILTER_VALIDATE_INT)) {
                throw new LocalizedException(__("Variable is not an integer"));
            }
        }

        return true;
    }
}
