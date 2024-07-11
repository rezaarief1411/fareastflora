<?php

namespace Smartosc\Cms\Ui\Component\Listing\Column;

use Magento\Framework\Url;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Smartosc\Cms\Helper\Data as Helper;

/**
 * Class PageActions
 *
 * @package Smartosc\Cms\Ui\Component\Listing\Column
 */
class PageActions
{
    /**
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    /**
     * @var \Smartosc\Cms\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface
     */
    protected $context;

    /**
     * PageActions constructor.
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\Url                                       $urlBuilder
     * @param \Smartosc\Cms\Helper\Data                                    $helper
     */
    public function __construct(
        ContextInterface $context,
        Url $urlBuilder,
        Helper $helper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->context = $context;
    }

    /**
     * @param $productActions
     * @param $result
     *
     * @return mixed
     */
    public function afterPrepareDataSource($productActions, $result)
    {
        if (!$this->helper->isEnableHideDeleteOption()) {
            return $result;
        }
        if (isset($result['data']['items'])) {
            foreach ($result['data']['items'] as &$item) {
                if (isset($item['page_id']) && isset($item['identifier']) && $item['identifier'] === "home") {
                    if (isset($item[$productActions->getData('name')]['delete'])) {
                        unset($item[$productActions->getData('name')]['delete']);
                    }
                }
            }
        }
        return $result;
    }
}

