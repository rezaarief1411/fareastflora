<?php

namespace Smartosc\Captcha\Observer;

use Magento\Captcha\Observer\CheckUserCreateObserver as DefaultCheckUserCreateObserver;

/**
 * Class CheckUserCreateObserver
 * @package Smartosc\Captcha\Observer
 */
class CheckUserCreateObserver extends DefaultCheckUserCreateObserver
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'user_create';
        $captchaModel = $this->_helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->captchaStringResolver->resolve($controller->getRequest(), $formId))) {
                $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->_session->setCustomerFormData($controller->getRequest()->getPostValue());
                $url = $this->_urlManager->getUrl('*/*/login');
                $controller->getResponse()->setRedirect($this->redirect->error($url));
            }
        }
        return $this;
    }
}
