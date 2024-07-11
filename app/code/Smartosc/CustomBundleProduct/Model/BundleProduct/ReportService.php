<?php

namespace Smartosc\CustomBundleProduct\Model\BundleProduct;

/**
 * Class ReportService
 * @package Smartosc\CustomBundleProduct\Model\BundleProduct
 */
class ReportService extends \Magento\Framework\Api\AbstractSimpleObject
    implements ReportServiceInterface
{
    /**
     * @var int
     */
    private $_product_id;

    /**
     * @var \MGS\Mpanel\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    const CONF_ENABLE = 'mpanel/conf_report_service/enable';

    const CONF_SUB_TOTAL = 'mpanel/conf_report_service/sub_total';

    const CONF_PRICE = 'mpanel/conf_report_service/price';

    const CONF_NAME = 'mpanel/conf_report_service/name';

    const CONF_ITEM_CODE = 'mpanel/conf_report_service/code';

    const CONF_IMAGE = 'mpanel/conf_report_service/image';

    const CONF_PRODUCT_ID = 'mpanel/conf_report_service/product_id';

    /**
     * ReportService constructor.
     * @param \MGS\Mpanel\Helper\Data $helper
     */
    public function __construct(
        \MGS\Mpanel\Helper\Data $helper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $data = array_merge($data, $this->getInitData());
        parent::__construct($data);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        if (!isset($this->_product_id)) {
            $this->_product_id = $this->helper->getStoreConfig(self::CONF_PRODUCT_ID);
        }
        return $this->_product_id;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        return $this->productRepository->getById(
            $this->getProductId()
        );
    }

    /**
     * @return array
     */
    protected function getInitData()
    {
        $mediaBaseUrl = $this->helper->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $image = $this->helper->getStoreConfig(self::CONF_IMAGE);
        $hasService = true;
        try {
            $product = $this->getProduct();
        } catch (\Exception $ex) {
            $hasService = false;
        }

        return [
            self::PRICE => $hasService ? $product->getPrice() : 3,
            self::NAME => $hasService ? $product->getName() : 'Repotting Charge',
            self::ITEM_CODE => $hasService ? $product->getSku() : 'Repotting Charge',
            self::IMAGE => $hasService && $image !== null ? sprintf('%s%s/%s', $mediaBaseUrl, 'wysiwyg/banner', $image): ''
        ];
    }

    /**
     * @param int $productId product id
     * @return bool
     */
    public function isReportServiceProduct($productId) {
        $config = $this->helper->getStoreConfig(self::CONF_PRODUCT_ID);

        return !$config || $productId == $config;
    }

    /**
     * @return mixed
     */
    public function getReportServiceProduct() {
        $productId = $this->helper->getStoreConfig(self::CONF_PRODUCT_ID);
        return $productId;
    }

    /**
     * @return bool
     */
    public function isEnableFeature() {
        return $this->helper->getStoreConfig(self::CONF_ENABLE);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getItemData() {

        return new \Magento\Framework\DataObject([
            'is_pseudo'=> true,
            'parent_item_id' => '',
            'sub_total' => 0,
            'parent_qty' => 0,
            'item_id' => '#',
            'configure_url' => '#',
            'is_visible_in_site_visibility' => true,
            'product_id' => $this->getProductId(),
            'product_name' => $this->getName(),
            'product_sku' => $this->getItemCode(),
            'product_url' => '#',
            'product_has_url' => false,
            'product_price' => $this->checkoutHelper->formatPrice($this->getPrice()),
            'product_price_value' => $this->getPrice(),
            'canApplyMsrp' => false,
            'product_image' => [
                'src' => $this->getImage(),
                'alt' => $this->getName(),
                'width' => 90,
                'height' => 90
            ],
            'reportServiceProductId' => $this->isReportServiceProduct($this->getProductId())
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->_data[self::PRICE];
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
       return $this->_data[self::NAME];
    }

    /**
     * {@inheritdoc}
     */
    public function getItemCode()
    {
        return $this->_data[self::ITEM_CODE];
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
       return $this->_data[self::IMAGE];
    }
}
