<?php

namespace Smartosc\Blog\Block\Category;

/**
 * Class Posts
 * @package Smartosc\Blog\Block\Category
 */
class Posts extends \MGS\Blog\Block\Category\Posts{
    /**
     * @var \Smartosc\Blog\Helper\Data
     */
    protected $helperData;

    /**
     * Posts constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MGS\Blog\Helper\Data $blogHelper
     * @param \MGS\Blog\Model\Post $post
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Smartosc\Blog\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        \Magento\Framework\App\Http\Context $httpContext,
        \Smartosc\Blog\Helper\Data $helperData,
        array $data = []
    ) {
        $this->sorter = $helperData;
        parent::__construct($context, $registry, $blogHelper, $post, $httpContext, $data);
    }

    /**
     * @return \MGS\Blog\Block\Category\Posts|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $category = $this->getCurrentCategory();
        $pageTitle = $category->getTitle();
        $metaKeywords = $category->getMetaKeywords();
        $metaDescription = $category->getMetaDescription();
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blog-post-list');
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        if ($metaKeywords) {
            $this->pageConfig->setKeywords($metaKeywords);
        }
        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'blog.post.list.custom_pager'
            );
            $limit = $this->helperData->getLimits();
            $pager->setLimit($limit)->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
        }
    }
}
