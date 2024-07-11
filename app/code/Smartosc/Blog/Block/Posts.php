<?php

namespace Smartosc\Blog\Block;

use Smartosc\Blog\Block\Html\Sorter;

/**
 * Class Posts
 *
 * Block Posts
 */
class Posts extends \MGS\Blog\Block\Posts
{
    /**
     *
     * @var \Smartosc\Blog\Model\Post\Search
     */
    protected $searchModel;

    /**
     * @var \Smartosc\Blog\Helper\Data
     */
    protected $helper;

	/**
	 * @var \Smartosc\Blog\Block\Html\Sorter
	 */
    protected $sorter;

    /**
     * {@inheritDoc}
     * @see \MGS\Blog\Block\Posts::__construct()
     */
    public function __construct(
        \Smartosc\Blog\Model\Post\Search $search,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \Smartosc\Blog\Helper\Data $helper,
        \MGS\Blog\Model\Post $post,
        \Magento\Framework\App\Http\Context $httpContext,
        \Smartosc\Blog\Block\Html\Sorter $sorter,
        array $data = []
    ) {
        $this->searchModel = $search;
        $this->helper = $helper;
        $this->sorter = $sorter;
        parent::__construct($context, $registry, $blogHelper, $post, $httpContext, $data);
    }
    
    /**
     * {@inheritDoc}
     * @see \MGS\Blog\Block\Posts::_construct()
     */
    public function _construct()
    {
        parent::_construct();
        /* @var \MGS\Blog\Model\Resource\Post\Collection $postCollection */
        if ($this->getRequest()->getParam('q')!='') {
            $searchQuery = $this->getRequest()->getParam('q');

            $postCollection = $this->getSearchModel()->setSearchQuery($searchQuery)->getPostCollection();
        } else {
            $post = $this->_post;
            $postCollection = $post->getCollection()
                ->addFieldToFilter('status', 1)
                ->addStoreFilter($this->_storeManager->getStore()->getId());

        }

        if ($this->getRequest()->getParam('sort')!='') {
            $sort = $this->getRequest()->getParam('sort');

            switch ($sort) {
                case 'oldest-to-newest':
                    $postCollection->setOrder('created_at', 'ASC');
                    break;
                case 'newest-to-oldest':
                    $postCollection->setOrder('created_at', 'DESC');
                    break;
                case 'a-z':
                    $postCollection->setOrder('title', 'ASC');
                    break;
                case 'z-a':
                    $postCollection->setOrder('title', 'DESC');
                    break;
                default:
            }

        } else {
            $postCollection->setOrder('created_at', $this->getConfig('general_settings/default_sort'));
        }
        
        if ($this->getRequest()->getParam('category')!='') {
            $categoryUrlKey = $this->getRequest()->getParam('category');
            $categoryId = $this->helper->getCategoryId($categoryUrlKey);

            $postCollection->addCategoryFilter($categoryId);
        }

        $this->setCollection($postCollection);
    }
    
    /**
     * @return \Smartosc\Blog\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * {@inheritDoc}
     * @see \MGS\Blog\Block\Posts::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getCollection()) {
            $postCollection = $this->getCollection();

            $finder = $this->getLayout()->createBlock(
                \Smartosc\Blog\Block\Html\Finder::class,
                'blog.post.list.finder'
            );

            /** @var \Smartosc\Blog\Block\Html\Sorter $sorter */
            $sorter = $this->getLayout()->createBlock(
                \Smartosc\Blog\Block\Html\Sorter::class,
                'blog.post.list.sorter'
            );
            $this->unsetChild('pager');
            $pager = $this->getLayout()->createBlock(
                \Smartosc\Blog\Block\Html\Pager::class,
                'blog.post.list.custom_pager'
            );

            if ($this->getRequest()->getParam('limit')!='') {
                $limit= $this->getRequest()->getParam('limit');
            } else {
                $limit= $this->getLimits();
            }
            $pager->setLimit($limit)->setCollection($postCollection);
            $sorter->setCollection($postCollection);
            $this->setChild('sorter', $sorter);
            $this->setChild('finder', $finder);
            $this->setChild('pager', $pager);
        }
        
        return $this;
    }
    
    /**
     * @return \Smartosc\Blog\Model\Post\Search
     */
    public function getSearchModel()
    {
        return $this->searchModel;
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
	 * @return mixed
	 */
    public function getLimits(){
    	$limits = $this->sorter->getPostsPerPage();
    	asort($limits);
	    $sorts = array_keys($limits);
    	return $sorts[0];
    }
}
