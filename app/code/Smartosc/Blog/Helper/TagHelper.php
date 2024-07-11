<?php
namespace Smartosc\Blog\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data;
use Smartosc\Blog\Block\Tag\Posts;

/**
 * Class TagHelper
 * @package Smartosc\Blog\Helper
 */
class TagHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Smartosc\Blog\Block\Tag\Posts
     */
    protected $tag;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * CartItemHelper constructor.
     * @param Context $context
     * @param Data $jsonHelper
     * @param Posts $tag
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        Posts $tag
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->tag = $tag;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getCurrentTag()
    {
        return $this->tag->getCurrentTags();
    }
}
