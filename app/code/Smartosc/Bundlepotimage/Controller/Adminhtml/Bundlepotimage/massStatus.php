<?php

namespace Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage;

use Magento\Backend\App\Action;

/**
 * Class MassStatus
 * @package Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
 */
class MassStatus extends \Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getPost('ids');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->_objectManager->create(\Smartosc\Bundlepotimage\Model\Bundlepotimage::class)
                        ->load($id)
                        ->setStatus($this->getRequest()->getPost('status'))
                        ->save();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were successfully updated.', count($ids)));

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
