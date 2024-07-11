<?php

namespace Smartosc\Mpanel\Block\Product\ProductList;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;

/**
 * Class Toolbar
 * @package Smartosc\Mpanel\Block\Product\ProductList
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    const COOKIE_SORT_CRITERIA = 'current_sort_criteria';

    /**
     * Toolbar constructor.
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param \Magento\Framework\App\Http\Context|null $httpContext
     * @param \Magento\Framework\Data\Form\FormKey|null $formKey
     */
    public function __construct(\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager, \Magento\Framework\View\Element\Template\Context $context, \Magento\Catalog\Model\Session $catalogSession, \Magento\Catalog\Model\Config $catalogConfig, ToolbarModel $toolbarModel, \Magento\Framework\Url\EncoderInterface $urlEncoder, ProductList $productListHelper, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, array $data = [], ToolbarMemorizer $toolbarMemorizer = null, \Magento\Framework\App\Http\Context $httpContext = null, \Magento\Framework\Data\Form\FormKey $formKey = null)
    {
        $this->cookieManager = $cookieManager;
        parent::__construct($context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper, $data, $toolbarMemorizer, $httpContext, $formKey);
    }
    /**
     * @return string
     */
    public function getCurrentSortCriteria()
    {
        if ($this->_request->getParam('product_list_order') && !$this->getRequest()->isAjax()) {
            return $this->_request->getParam('product_list_order');
        }
        $default = 'created_at_desc';
        $cookie = $this->getCookie(self::COOKIE_SORT_CRITERIA);
        $cookieParse = json_decode($cookie, true);
        if ($cookie != "" && isset($cookieParse['product_list_order']) && isset($cookieParse['product_list_dir'])):
            return $cookieParse['product_list_order'] . '_' . $cookieParse['product_list_dir'];
        endif;
        return $default;
    }

    /**
     * @param string $name
     * @return string
     */
    private function getCookie($name)
    {

        return $this->cookieManager->getCookie($name);
    }
}
