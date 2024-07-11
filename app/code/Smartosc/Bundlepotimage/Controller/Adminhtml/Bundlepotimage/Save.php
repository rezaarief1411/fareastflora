<?php

namespace Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage;

use Magento\Backend\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Save
 * @package Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
 */
class Save extends \Smartosc\Bundlepotimage\Controller\Adminhtml\Bundlepotimage
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->productRepository = $productRepository;
        $this->_resource = $resource;
        parent::__construct($context);
    }

    /**
     * {@inheritDoc}}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        if ($data) {

            try {
                $bundleSku = $data['bundle_sku'];
                $potSku = $data['pot_sku'];

                try {
                    $bundle = $this->productRepository->get($bundleSku);
                    $pot = $this->productRepository->get($potSku);
                } catch (\Exception $e) {
                    $errors = [];
                    $errors[] = __('Bundle SKU or Pot SKU not valid');
                    $errors[] = $e->getMessage();
                    throw new \Exception(implode('. ', $errors));
                }

                $connection = $this->_resource->getConnection();
                $select = $connection->select()
                                     ->from('catalog_product_bundle_selection')
                                     ->where('parent_product_id = ?', $bundle->getId())
                                     ->where('product_id = ?', $pot->getId());

                $dbStatement = $connection->query($select);
                $selection=[];
                while ($item = $dbStatement->fetch()) {
                    $selection[] = $item;
                }

                if (count($selection) == 0) {
                    throw new \Exception(__('selection not found'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }

            if (isset($data['identifier']) && !isset($data['id'])) {
                $data['bundle_id'] = $bundle->getId();
                $data['pot_id'] = $pot->getId();
                $connection = $this->_resource->getConnection();
                $select = $connection->select()
                    ->from(
                        'bundle_pot_image'
                    )
                             ->where('bundle_id = ?', $bundle->getId())
                             ->where('pot_id = ?', $pot->getId());
                         $dbStatement = $connection->query($select);
                         $result=[];
                while ($item = $dbStatement->fetch()) {
                    $result[] = $item;
                }
                if (count($result)>0) {
                    $this->messageManager->addError(__('Preview image already exist. Please try again'));
                    return $resultRedirect->setPath('*/*/');
                }
                $existBundlepotimages = $this->_objectManager
                    ->create(\Smartosc\Bundlepotimage\Model\Bundlepotimage::class)
                    ->getCollection()
                    ->addFieldToFilter('identifier', $data['identifier']);
                if (count($existBundlepotimages)>0) {
                    $this->messageManager->addError(__('Identifier already exist. Please use other identifier'));
                    return $resultRedirect->setPath('*/*/');
                }

            }
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create(\Smartosc\Bundlepotimage\Model\Bundlepotimage::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            // init model and set data
            $data['bundle_id'] = $bundle->getId();
            $data['pot_id'] = $pot->getId();
            $data['bundle_name'] = $bundle->getName();
            $data['pot_name'] = $pot->getName();
            $data['selection_id'] =  $selection[0]['selection_id'];
            $data['option_id'] = $selection[0]['option_id'];
            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('You saved the item.'));
                // clear previously saved data from session
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
