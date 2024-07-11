<?php

namespace Smartosc\Blog\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \MGS\Blog\Model\Resource\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \MGS\Blog\Helper\Data
     */
    protected $blogHelper;

    /**
     * @var array
     */
    protected $_availableLimit = [20 => 20, 30 => 30, 40 => 40, 50 => 50];

    const POST_PER_PAGE = 'general_settings/posts_per_page';

    /**
     * Data constructor.
     * @param \MGS\Blog\Model\Resource\Category\CollectionFactory $categoryCollectionFactory
     * @param Context $context
     */
    public function __construct(
        \MGS\Blog\Model\Resource\Category\CollectionFactory $categoryCollectionFactory,
        \MGS\Blog\Helper\Data $blogHelper,
        Context $context
    ) {
        $this->blogHelper = $blogHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @param string $urlKey
     * @return int
     */
    public function getCategoryId($urlKey)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter('url_key', $urlKey);

        $categoryId = (int)$collection->getFirstItem()->getCategoryId();

        return $categoryId;
    }

    /**
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAllCategory()
    {
        return $this->categoryCollectionFactory->create()
            ->addFieldToSelect(['category_id', 'title', 'url_key'])
            ->addFieldToFilter('status', ['eq' => 1])
            ->getItems();
    }

    /**
     *
     * @param \MGS\Blog\Model\Post $post
     * @return array
     */
    public function getRelateCategory($post)
    {
        $category = [];
        $collection = $post->getCatetories();

        foreach ($collection as $item) {

            if ($item->getUrlKey() == 'all') {
                continue;
            }

            $category = [
                'url_key' => $item->getUrlKey(),
                'title' => $item->getTitle(),
                'id' => $item->getCategoryId()
            ];

            break;
        }

        return $category;
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

    /**
     * @return mixed
     */
    public function getLimits()
    {
        $limits = $this->getPostsPerPage();
        asort($limits);
        $sorts = array_keys($limits);
        return $sorts[0];
    }
}
