<?php

namespace Smartosc\Blog\Block\Tag;

/**
 * Class Posts
 * @package Smartosc\Blog\Block\Tag
 */
class Posts extends \MGS\Blog\Block\Tag\Posts
{
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
        $this->helperData = $helperData;
        parent::__construct($context, $registry, $blogHelper, $post, $httpContext, $data);
    }

    /**
     * @return \MGS\Blog\Block\Tag\Posts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $tag = $this->getCurrentTag();
        $pageTitle = __('Tag \'%1\'', $tag);
        $metaKeywords = $tag;
        $metaDescription = $tag;
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
            $finder = $this->getLayout()->createBlock(
                \Smartosc\Blog\Block\Html\Finder::class,
                'blog.tag.list.finder'
            );
            /** @var \Smartosc\Blog\Block\Html\Sorter $sorter */
            $sorter = $this->getLayout()->createBlock(
                \Smartosc\Blog\Block\Html\Sorter::class,
                'blog.tag.list.sorter'
            );
            $pager = $this->getLayout()->createBlock(
                \Smartosc\Blog\Block\Html\Pager::class,
                'blog.tag.list.custom_pager'
            );
            $limit = $this->helperData->getLimits();
            $collection = $this->getCollection();
            $sorter->setCollection($collection);
            $this->setChild('finder', $finder);
            $pager->setLimit($limit)->setCollection($collection);
            $this->setChild('sorter', $sorter);
            $this->setChild('pager', $pager);
        }
    }

    /**
     * @return string
     */
    public function getFinderHtml()
    {
        return $this->getChildHtml('finder');
    }

    /**
     * @return string
     */
    public function getSorterHtml()
    {
        return $this->getChildHtml('sorter');
    }

    /**
     * @return string
     */
    public function getCurrentTags()
    {
        return $this->getCurrentTag();
    }
}
