<?php

namespace Smartosc\Mpanel\Model\Config\Backend;

/**
 * Class Image
 * @package Smartosc\Mpanel\Model\Config\Backend
 */
class Image extends \Magento\Config\Model\Config\Backend\Image
{
    const UPLOAD_DIR = 'wysiwyg/banner';

    /**
     * @var int
     */
    protected $_maxFileSize = 4000;

    /**
     * {@inheritdoc}
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * {@inheritdoc}
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }
}
