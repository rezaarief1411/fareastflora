<?php

namespace Smartosc\Blog\Block\Html;

/**
 * Class Finder
 *
 * Block Finder
 */
class Finder extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Smartosc_Blog::html/finder.phtml';
    
    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $_collection;
    
    /**
     * @var string
     */
    protected $_pageVarName = 'q';
}
