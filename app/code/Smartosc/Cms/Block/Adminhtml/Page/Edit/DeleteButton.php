<?php

namespace Smartosc\Cms\Block\Adminhtml\Page\Edit;

use Magento\Cms\Block\Adminhtml\Page\Edit\DeleteButton as CoreDeleteButton;

/**
 * Class DeleteButton
 *
 * @package Smartosc\Cms\Block\Adminhtml\Page\Edit
 */
class DeleteButton extends CoreDeleteButton
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $data = [];
        $pageId = $this->getPageId();
        if ($pageId) {
            $page = $this->pageRepository->getById($pageId);
            if ($page->getIdentifier() !== "home") {
                $data = parent::getButtonData();
            }
        }
        return $data;
    }
}
