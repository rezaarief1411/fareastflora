<?php

namespace Smartosc\Customer\Block\Form\Login;

/**
 * Class Info
 * @package Smartosc\Customer\Block\Form\Login
 */
class Info extends \Magento\Customer\Block\Form\Login\Info
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Info constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context, $registration, $customerUrl, $checkoutData, $coreUrl, $data);
    }

    /**
     * @return bool
     */
    public function isNewsletterEnabled()
    {

        return true;
    }

    /**
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->_customerSession->getCustomerFormData(true);
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int)$data['region_id'];
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostActionUrl()
    {
        return $this->_customerUrl->getRegisterPostUrl();
    }

    /**
     * @return mixed
     */
    public function getCustomerPasswordValidate()
    {
        return $this->_scopeConfig->getValue('smartosc_customer_password/password/regular_expression');
    }
}
