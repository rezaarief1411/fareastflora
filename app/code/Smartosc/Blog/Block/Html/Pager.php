<?php

namespace Smartosc\Blog\Block\Html;

/**
 * Class Pager
 *
 * A Class extending from Magento Pager
 */
class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Smartosc_Blog::html/pager.phtml';
}
