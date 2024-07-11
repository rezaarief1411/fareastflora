<?php

namespace Smartosc\Blog\Model\Post;

use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Search
 *
 * Search Model
 */
class Search extends \Magento\Framework\Model\AbstractModel
{
    /**
     *
     * @var \MGS\Blog\Model\Resource\Post\Collection
     */
    protected $postCollection;

    /**
     * @var string
     */
    protected $_searchQuery;

    /**
     * @var string
     */
    protected $_order;

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->_order = $order;
    }

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MGS\Blog\Helper\Data
     */
    protected $_blogHelper;

    /**
     * @param $value
     * @return $this
     */
    public function setSearchQuery($value)
    {
        $this->_searchQuery = $value;
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \Magento\Framework\Model\AbstractModel::__construct()
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \MGS\Blog\Model\Resource\Post\CollectionFactory $postCollectionFactory,
        \MGS\Blog\Helper\Data $blogHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_blogHelper = $blogHelper;
        $this->postCollection = $postCollectionFactory->create();
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return \MGS\Blog\Model\Resource\Post\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPostCollection()
    {
        
        $searchQuery = $this->_searchQuery;
        
        $collection = $this->postCollection->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', $this->getConfig('general_settings/default_sort'));

        $collection->addFieldToFilter('title', ['like'=>"%$searchQuery%"]);

        return $collection;
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getConfig($key, $default = '')
    {
        $result = $this->_blogHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }
}
