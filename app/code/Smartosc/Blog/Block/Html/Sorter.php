<?php

namespace Smartosc\Blog\Block\Html;

use Magento\Framework\View\Element\Template;

/**
 * Class Sorter
 *
 * Blog Sorter
 */
class Sorter extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MGS\Blog\Helper\Data
     */
    protected $blogHelper;

    /**
     * @var \Smartosc\Blog\Helper\Data
     */
    protected $helper;

    /**
     * @var int
     */
    protected $_limit;
    
    /**
     * @var string
     */
    protected $_sort;

    /**
     * @var string
     */
    protected $_limitVarName = 'limit';
    
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Smartosc_Blog::html/sorter.phtml';
    
    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $_collection;
    
    /**
     * @var string
     */
    protected $_pageVarName = 'p';

    /**
     * Url Fragment for pagination
     *
     * @var string|null
     */
    protected $_fragment = null;

    /**
     * @var array
     */
    protected $_availableLimit = [20 => 20, 30 => 30, 40 => 40, 50 => 50];
    
    /**
     * @var array
     */
    protected $_availableSortBy =
    [
        'newest-to-oldest' => 'Newest to Oldest',
        'oldest-to-newest' => 'Oldest to Newest',
        'a-z'=>'Title A to Z',
        'z-a'=> 'Title Z to A'
    ];

    protected $request;

    const POST_PER_PAGE = 'general_settings/posts_per_page';

    const PATTERN_CATEGORY = '%s@{%d,%s}';

    /**
     * Sorter constructor.
     * @param \MGS\Blog\Helper\Data $blogHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \MGS\Blog\Helper\Data $blogHelper,
        \Smartosc\Blog\Helper\Data $helper,
        Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->blogHelper = $blogHelper;
        $this->helper = $helper;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getAllCategory()
    {
        $result = [];
        // get all categories
        $categories = $this->helper->getAllCategory();

        foreach ($categories as $category) {
            $title = $category->getTitle();
            $id = $category->getCategoryId();
            $urlKey = $category->getUrlKey();
            $result[$id] = sprintf(self::PATTERN_CATEGORY, $title, $id, $urlKey);
        }

        usort($result, function ($a, $b) {
            return $a > $b;
        });

        return $result;
    }

    /**
     * @return array
     */
    public function getAvailableCategory()
    {
        $collection = $this->getCollection();
        $categories =[];
        $AvailableCategory = [];

        foreach ($collection as $item) {
            /* @var \MGS\Blog\Model\Post $item  */

            /* @var \MGS\Blog\Model\Resource\Category\Collection $categoriesLookup */
            $categoriesLookup = $item->getCatetories();

            /* @var \MGS\Blog\Model\Category $cateItem */

            foreach ($categoriesLookup as $cateItem) {
                $categoryTitle = $cateItem->getTitle();
                $categoryId = $cateItem->getCategoryId();
                $urlKey = $cateItem->getUrlKey();
                if ($categoryTitle && !in_array($categoryTitle, $categories)) {
                    $categories[] = $categoryTitle;
                    $AvailableCategory[$categoryId] = sprintf(
                        self::PATTERN_CATEGORY,
                        $categoryTitle,
                        $categoryId,
                        $urlKey
                    );
                }
            }
        }
        usort($AvailableCategory, function ($a, $b) {
            return $a > $b;
        });

        return $AvailableCategory;
    }

    /**
     * @return array
     */
    public function getAvailableLimit()
    {
        $limits = $this->getPostsPerPage();
        $this->sortArrayLimits($limits);

        $this->_availableLimit = $limits;
        return $this->_availableLimit;
    }

    /**
     * @param $params
     */
    protected function sortArrayLimits(&$params)
    {
        $result = [];

        $keys = array_keys($params);
        sort($keys);

        foreach ($keys as $key) {
            $result[$key] = $params[$key];
        }

        $params = $result;
    }

    /**
     * @param \Magento\Framework\Data\Collection $collection
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableSortBy()
    {
        return $this->_availableSortBy;
    }
    
    /**
     * Retrieve name for pager limit data
     *
     * @return string
     */
    public function getLimitVarName()
    {
        return $this->_limitVarName;
    }
    
    /**
     * Retrieve page URL by defined parameters
     *
     * @param array $params
     *
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_fragment'] = $this->getFragment();
        $urlParams['_query'] = $params;
        
        return $this->getUrl($this->getPath(), $urlParams);
    }
    
    /**
     * Get Sort by url
     *
     * @param string $sort
     *
     * @return string
     */
    public function getSortByUrl($sort)
    {
        return $this->getPagerUrl(['sort' => $sort]);
    }

    /**
     * Get Category url
     *
     * @param string $urlKey
     *
     * @return string
     */
    public function getCategoryUrl($urlKey)
    {
        return $this->getPagerUrl(['category' => $urlKey]);
    }
    
    /**
     * Get limit url
     *
     * @param int $limit
     *
     * @return string
     */
    public function getLimitUrl($limit)
    {
        return $this->getPagerUrl([$this->getLimitVarName() => $limit]);
    }
    
    /**
     * Get the URL fragment
     *
     * @return string|null
     */
    public function getFragment()
    {
        return $this->_fragment;
    }
    
    /**
     * Set the URL fragment
     *
     * @param string|null $fragment
     * @return $this
     */
    public function setFragment($fragment)
    {
        $this->_fragment = $fragment;
        return $this;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    protected function getPath()
    {
        return $this->_getData('path') ?: '*/*/*';
    }
    
    /**
     * Is limit current
     *
     * @param int $limit
     *
     * @return bool
     */
    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }
    
    /**
     * Is sort current
     *
     * @param string $sort
     *
     * @return bool
     */
    public function isSortByCurrent($sort)
    {
        return $sort == $this->getSort();
    }
    
    /**
     * Return current page limit
     *
     * @return int
     */
    public function getLimit()
    {
        if ($this->_limit !== null) {
            return $this->_limit;
        }

        $limits = $this->getAvailableLimit();

        if ($limit = $this->getRequest()->getParam($this->getLimitVarName())) {
            if (isset($limits[$limit])) {
                return $limit;
            }
        }

        // get predefined Posts Per page config
        $postPerPage = $this->getPostsPerPage();
        if (count($postPerPage) > 0) {
            asort($postPerPage);
        }
        
        $limits = array_keys($limits);
        return $limits[0];
    }

    /**
     * Return current page sort type
     *
     * @return string
     */
    public function getSort()
    {
        if ($this->_sort !== null) {
            return $this->_sort;
        }
        
        $sorts = $this->getAvailableSortBy();
        if ($sort = $this->getRequest()->getParam('sort')) {
            if (isset($sorts[$sort])) {
                return $sort;
            }
        }
        
        $sorts = array_keys($sorts);
        return $sorts[0];
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Retrieve total number of pages
     *
     * @return int
     */
    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * @param $subject
     * @return array
     */
    public function parseCategory($subject)
    {

        $find='@';
        $pos = strpos($subject, $find);
        $title = substr($subject, 0, $pos);
        $id = substr($subject, $pos+1);
        $id = str_replace('{', '', $id);
        $id = str_replace('}', '', $id);
        list($categoryId, $urlKey) = explode(',', $id);
        return [
            'id' => $categoryId,
            'title' => $title,
            'url_key' => $urlKey
        ];
    }

    public function getUrlCategory()
    {
        return $this->request->getParam('category');
    }

    /**
     * @return array|int[]
     */
    public function getPostsPerPage()
    {
        $result = [];
        $postPerPage = $this->blogHelper->getConfig(self::POST_PER_PAGE);
        if ($postPerPage) {
            $postPerPageArr = explode(',', $postPerPage);
            foreach ($postPerPageArr as $item) {
                $result[$item] = $item;
            }
        } else {
            $result = $this->_availableLimit;
        }
        return $result;
    }
}
