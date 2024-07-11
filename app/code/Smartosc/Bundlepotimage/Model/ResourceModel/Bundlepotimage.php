<?php

namespace Smartosc\Bundlepotimage\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class bundlepotimage
 * @package Smartosc\Bundlepotimage\Model\ResourceModel
 */
class bundlepotimage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bundle_pot_image', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreationTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Exception
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (isset($_FILES['bundle_pot_image']['name']) && $_FILES['bundle_pot_image']['name'] != '') {
            try {
                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'bundle_pot_image']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

            } catch (\Exception $e) {
                return $this;
            }
            $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('bundlepotimage/');

            $uploader->save($path);
            $fileName = $uploader->getUploadedFileName();

            if ($fileName) {
                $object->setData('bundle_pot_image', $fileName);
                $object->save();
            }
            return $this;
        }
    }
}
