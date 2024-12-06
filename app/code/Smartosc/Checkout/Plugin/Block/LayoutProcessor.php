<?php

namespace Smartosc\Checkout\Plugin\Block;


/**
 * Class LayoutProcessor
 * @package Smartosc\Checkout\Plugin\Block
 */
/**
 * Class LayoutProcessor
 * @package Smartosc\Checkout\Plugin\Block
 */
class LayoutProcessor
{
    const PREFIX = 'prefix';
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;
    /**
     * @var \Magento\Ui\Component\Form\AttributeMapper
     */
    protected $attributeMapper;
    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merge;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var
     */
    private $quote;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Smartosc\Checkout\Helper\SpecialDate
     */
    protected $specialDate;

    /**
     * @var \Smartosc\Preorder\Helper\Data
     */
    protected $preorderHelper;

    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    protected $customerSession;

    /**
     * @var \Smartosc\Checkout\Model\Form\Register
     */
    private $formRegister;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Customer\Model\ResourceModel\AddressRepository
     */
    private $addressRepository;

    /**
     * Logging instance
     * @var \Smartosc\Checkout\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Ui\Component\Form\AttributeMapper $attributeMapper
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Smartosc\Checkout\Helper\SpecialDate $specialDate
     * @param \Smartosc\Preorder\Helper\Data $preorderHelper
     * @param \Smartosc\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository
     * @param \Smartosc\Checkout\Logger\Logger $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Ui\Component\Form\AttributeMapper $attributeMapper,
        \Magento\Checkout\Block\Checkout\AttributeMerger   $merger,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session  $checkoutSession,
        \Smartosc\Checkout\Helper\SpecialDate $specialDate,
        \Smartosc\Preorder\Helper\Data $preorderHelper,
        \Smartosc\Checkout\Helper\Data $checkoutHelper,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository,
        \Smartosc\Checkout\Logger\Logger $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merge = $merger;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->specialDate = $specialDate;
        $this->preorderHelper = $preorderHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->customerSession = $session;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->_logger = $logger;
        $this->jsonHelper = $jsonHelper;

        if ($this->customerSession->isLoggedIn())
        {
            $customerId = $this->customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $billingAddressId = $customer->getDefaultBilling();

            try {
                $billingAddress = $this->addressRepository->getById($billingAddressId);
                $floor = $billingAddress->getCustomAttribute('floor');
                $building = $billingAddress->getCustomAttribute('building');
                if ($floor) {
                    $this->getQuote()->setData('billing_floor', $floor->getValue());
                }
                if ($building) {
                    $this->getQuote()->setData('billing_building', $building->getValue());
                }
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function aroundProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        \Closure $proceed,
        array $jsLayout
    ) {
        
        $jsLayoutResult = $proceed($jsLayout);

        if ($this->getQuote()->isVirtual()) {
            return $jsLayoutResult;
        }

        if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset'])) {
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['label'] = __('Address');
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['billingAddress']['children']['shipping-address-fieldset']['children']['street']['label'] = __('Address');


            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['label'] = __('Address');


            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['label'] = __('Address');
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['size'] = 1;




            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['billing-address']['children']['form-fields']['children']['street']['children'][0]['placeholder'] = __('Address');
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['billing-address']['children']['shipping-address-fieldset']['children']['street']['size'] = 1;

            $elements = $this->getAddressAttributes();

            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset'] = $this->getCustomBillingAddressComponent($elements);
            // hide caption of field Prefix
            if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['prefix']['label'])) {
                unset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['prefix']['label']);
            }

            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['street']['label'] = __('Address');
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['street']['sortOrder'] = 20;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['street']['size'] = 1;


            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['street']['children'][0]['label'] =  __('Address');
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['street']['children'][0]['value'] =  $this->checkoutSession->getQuote()->getShippingAddress()->getStreet();

            $street = '';
            if ($this->checkoutSession->getQuote()->getShippingAddress()->getStreet()
                && $this->checkoutSession->getQuote()->getShippingAddress()->getStreet()[0]){
                $street = $this->checkoutSession->getQuote()->getShippingAddress()->getStreet()[0];
            }

            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['street']['children'][0]['value'] =  $street;

            // postal code
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['postcode']['inputName'] = 'postalcode';
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['postcode']['value'] = $this->checkoutSession->getQuote()->getShippingAddress()->getPostcode();
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['postcode']['label'] = __('Postal Code');
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['postcode']['sortOrder'] = 35;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['postcode']['validation']['required-entry'] = true;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['postcode']['validation']['validate-number'] = 0;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['postcode']['validation']['validate-zip-us'] = true;

            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['country_id']['sortOrder'] = 40;

            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['country_id']['config']['elementTmpl'] = 'Smartosc_Checkout/form/element/smart_country';


            // telephone
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['telephone']['sortOrder'] = 50;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['telephone']['label'] = __('Telephone');
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['telephone']['value'] = $this->checkoutSession->getQuote()->getShippingAddress()->getTelephone();

            // city
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['city']['value'] = 'Singapore';
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['city']['visible'] = false;


            //"checkout.steps.shipping-step.shippingAddress.billingAddress.address-fieldset.region"
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['region']['visible'] = false;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['region']['value'] = '';
            //region_id
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['region_id']['visible'] = false;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['region_id']['value'] = '';

            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['firstname']['value'] = $this->checkoutSession->getQuote()->getShippingAddress()->getFirstname();
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billingAddress']['children']['address-fieldset']['children']['lastname']['value'] = $this->checkoutSession->getQuote()->getShippingAddress()->getLastname();

            if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['afterMethods']['children']['billing-address-form'])) {
                unset($jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['afterMethods']['children']['billing-address-form']);
            };

            if ($billingAddressForms = $jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']) {
                foreach ($billingAddressForms as $billingAddressFormsKey => $billingAddressForm) {
                    if ($billingAddressFormsKey != 'before-place-order') {
                        unset($jsLayoutResult['components']['checkout']['children']['steps']['children']['billing-step']['children']
                            ['payment']['children']['payments-list']['children'][$billingAddressFormsKey]);
                    }
                }
            }

            // customize shipping address
            //"checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset"

            // customize & sort
            if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix'])) {

                // remove preset values
                if (isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix']['options'])) {
                    $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix']['options'] = [];
                }

                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix']['options'][] = ['value' => 'Mr', 'label' => __('Mr.')];
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix']['options'][] = ['value' => 'Mrs', 'label' => __('Mrs.')];
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix']['options'][] = ['value' => 'Mdm', 'label' => __('Mdm.')];
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix']['options'][] = ['value' => 'Ms', 'label' => __('Ms.')];
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['prefix']['options'][] = ['value' => 'Dr', 'label' => __('Dr.')];
            }
            // [Shipping Address Form] Add field floor/unit , building
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset'] = $this->getCustomShippingAddressComponent($elements);

            // unset billing email address
            unset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['billing_email']);

            //first name
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['firstname'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'additionalClasses' => 'firstname',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                ],
                'dataScope' => 'shippingAddress.firstname',
                'label' => 'First Name',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => ['required-entry' => true],
            ];
            //last name
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['lastname'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'additionalClasses' => 'lastname',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                ],
                'dataScope' => 'shippingAddress.lastname',
                'label' => 'Last Name',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => ['required-entry' => true],
            ];
            // street
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['street'] = [
                'component' => 'Magento_Ui/js/form/components/group',
                'dataScope' => 'shippingAddress.street',
                'provider' => 'checkoutProvider',
                'type' => 'group',
                'additionalClasses' => 'street',
                'children' => [
                    [
                        'label' => __('Address'),
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'config' => [
                            'customScope' => 'shippingAddress',
                            'template' => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input'
                        ],
                        'dataScope' => '0',
                        'provider' => 'checkoutProvider',
                        'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 255],
                    ],
                ]
            ];
            // building name
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['building'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'additionalClasses' => 'building',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                ],
                'dataScope' => 'shippingAddress.building',
                'label' => 'Building Name',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => ['required-entry' => false],
            ];
            // floor / unit
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['floor'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'additionalClasses' => 'floor',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                ],
                'dataScope' => 'shippingAddress.floor',
                'label' => 'Floor/Units',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => ['required-entry' => true],
            ];
            // city
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city']['value'] = 'Singapore';
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city']['visible'] = false;
            // @todo: country Sg default
            //$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id'] = '';

            // region
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region']['value'] = '';
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region']['visible'] = false;
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['value'] = '';
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['visible'] = false;

//            // postal code
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'additionalClasses' => 'postcode',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                ],
                'dataScope' => 'shippingAddress.postcode',
                'label' => 'Postal Code',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [
                    'required-entry' => true,
                    'validate-number' => 0,
                    'validate-zip-us' => true
                ],
            ];

            // phone
            $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'additionalClasses' => 'telephone',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                ],
                'dataScope' => 'shippingAddress.telephone',
                'label' => 'Telephone',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => ['required-entry' => true],
            ];

            /*Delivery Time
              Delivery Date
              Delivery Comment*/

            if (!isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['delivery-extra-information'])) {
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['delivery-extra-information'] = [];

                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['delivery-extra-information'] = $this->getDeliveryScheduleComponent($this->getDeliveryScheduleAttributes());

                $elementTml = &$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['delivery-extra-information'];

                if (isset($elementTml['children']['pickup_comments']['config']['elementTmpl'])) {
                    $elementTml['children']['pickup_comments']['config']['elementTmpl'] = 'Smartosc_Checkout/form/element/smart_note';
                }

                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['delivery-extra-information']['children']['pickup_location'] = [
                    'component' => 'Smartosc_Checkout/js/delivery-schedule-location',
                    'label' => __('Pickup location'),
                ];
            }

            if (!isset($jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['smart-gift'])) {
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['smart-gift'] = [];

                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['smart-gift'] = $this->getSmartGiftComponent($this->getSmartGiftAttributes());

                // gift container
                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['smart-gift']['children']['gift-container']['component'] = 'Smartosc_Checkout/js/view/smart-gift';

                /*$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['smart-gift']['children']['gift-container']['label'] = __('Gift container');*/

                $jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['smart-gift']['sortOrder'] = 2;
            }
        }

        // add field delivery date to delivery methods block in step delivery
        $deliveryStepUiComponent = &$jsLayoutResult['components']['checkout']['children']['steps']['children']['delivery-step'];

        $deliveryDateAttributes = $this->getDeliveryDateAttributes();
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_date'] = $this->getDeliveryDateComponent($deliveryDateAttributes);
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_date']['children']['delivery_date']['placeholder'] = __('Please select a date');
        $deliveryStepUiComponent['children']['deliveryContent']['children']['delivery_date']['sortOrder'] = 5;

        /**
         ** Display Delivery Date
         ** Display Delivery Note
         ** In Deliver Step
         **/
        $deliveryNoteElements = $this->getDeliveryNoteAttributes();
        $deliveryContent = &$deliveryStepUiComponent['children']['deliveryContent'];

        $deliveryContent['children']['delivery_note'] = [];

        $deliveryContent['children']['delivery_note'] = $this->getDeliveryNoteComponent($this->getDeliveryNoteAttributes());


        $deliveryContent['children']['delivery_note']['children']['note-container']['component'] = 'Smartosc_Checkout/js/view/delivery-note';


        $deliveryContent['children']['delivery_note']['sortOrder'] = 6;

        $deliveryContent['children']['delivery_note']['children']['delivery_note']['config']['placeholder'] = __('Please enter a delivery note');
        if(isset($deliveryContent['children']['delivery_note']['children']['delivery_note']['config']['elementTmpl'])) {
            $deliveryContent['children']['delivery_note']['children']['delivery_note']['config']['elementTmpl'] = 'Smartosc_Checkout/form/element/smart_note';
        }

        if ($this->formRegister === null) {
            $this->formRegister = \Magento\Framework\App\ObjectManager::getInstance()
                                                                      ->get(\Smartosc\Checkout\Model\Form\Register::class);
        }
        $jsLayoutResult = array_merge_recursive($jsLayoutResult, [
            'components' => [
                'checkout' => [
                    'children' => [
                        'steps' => [
                            'children' => [
                                'login-step' => [
                                    'children' => [
                                        'login-config' => [
                                            'children' => [
                                                'customer-email' => [
                                                    'children' => [
                                                        'prefix' => $this->formRegister->getRendererArray('prefix')
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        return $jsLayoutResult;
    }

    /**
     * @return false|string
     */
    public function getAvailableFrom()
    {
        $arrayPickup = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $date = $objDate->gmtDate();
        $items = $this->cart->getQuote()->getAllItems();
        foreach ($items as $key => $item) {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
            $pickupDate = $product->getData('available_from_date');
            $arrayPickup[] = $pickupDate;
        }
        $sumTime = abs(strtotime(max($arrayPickup)) - strtotime($date));
        $sumDay = ceil($sumTime / (60 * 60 * 24));

        $dateArrayPickup = date('Y-m-d', strtotime(max($arrayPickup)));

        $dateToday = date('Y-m-d', strtotime($date));

        if (strtotime($dateArrayPickup) < strtotime($dateToday) || !max($arrayPickup)) {
            $blockDays = $this->checkoutHelper->getBlockDays() . ' day';
            return date('d', strtotime($blockDays, $sumDay));
        } else {
            return $sumDay;
        }
    }

    /**
     * @return false|string
     */
    public function getDateTime()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $date = $objDate->gmtDate();
        $items = $this->cart->getQuote()->getAllItems();
        foreach ($items as $key => $item) {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
            $pickupDate = $product->getData('pickup_date');
            $arrayPickup[] = $pickupDate;
        }
        $dateArrayPickup = date('Y-m-d', strtotime(max($arrayPickup)));

        $dateToday = date('Y-m-d', strtotime($date));
        $specialDisableDate = $this->getSpecialDisableDate();
        $farthestPreorderShippingDate = $this->preorderHelper->getFarthestPreorderShippingDay();
        if (strtotime($dateArrayPickup) < strtotime($dateToday) || !max($arrayPickup)) {
            $minDate = date('Y-m-d', strtotime('4 day', strtotime($date)));
            if (strtotime($farthestPreorderShippingDate) > strtotime($minDate)){
                $minDate = date('Y-m-d', strtotime($farthestPreorderShippingDate));
            }
            if (in_array(strtotime($minDate), $specialDisableDate)) {
                $minDate = date('Y-m-d', strtotime('1 day', strtotime($minDate)));
            }
            return $minDate;
        } else {
            return date('Y-m-d', strtotime(max($arrayPickup)));
        }
    }

    /**
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @param $elements
     * @return array
     */
    protected function getDeliveryDateComponent($elements)
    {
        $components = [
            'component' => 'uiComponent',
            'displayArea' => 'delivery-date',
            'children' => $this->merge->merge(
                $elements,
                'checkoutProvider',
                'delivery-date',
                []
            )
        ];

        return $components;
    }

    /**
     * @param $elements
     * @return array
     */
    protected function getDeliveryNoteComponent($elements)
    {
        $components = [
            'component' => 'uiComponent',
            'displayArea' => 'delivery_note',
            'children' => $this->merge->merge(
                $elements,
                'checkoutProvider',
                'delivery_note',
                []
            )
        ];

        return $components;
    }


    /**
     * @return array
     */
    private function getDeliveryNoteAttributes()
    {
        $elements= [];

        $elements['delivery_note'] = $this->addFieldToLayout('delivery_note', [
            'component' => 'textarea',
            'validation' => ['required-entry' => false],
            'config' => [
                'customScope' => null,
                'elementTmpl' => 'ui/form/element/textarea'
            ],
            'dataType' => 'text',
            'formElement' => 'textarea',
            'label' => __('Delivery Note'),
            'placeholder' => __('Delivery Note')
        ]);

        return $elements;
    }

    /**
     * @return array
     */
    private function getDeliveryDateAttributes()
    {
        $elements= [];

        $elements['delivery_date'] = $this->addFieldToLayout('delivery_date', [
            'component' => 'uiComponent',
            'dataType' => 'string',
            'label' => __('Delivery Date'),
            'formElement' => 'smart_date',
            'source' => 'checkoutProvider',
            'validation' => ['required-entry' => true],
            'config' => [
                'customScope' => null,
                'customEntry' => null,
                'template' => 'ui/form/field',
            ],
            'value' => $this->getDateTime(),
            'options' => [
                'minDate' => $this->getAvailableFrom(),
                'dateFormat' => 'dd-MMM-yyyy'
            ],
        ]);


        return $elements;
    }

    /**
     * @return array
     */
    private function getDeliveryScheduleAttributes()
    {


        $elements = [];
        $elements['pickup_date'] = $this->addFieldToLayout('pickup_date', [
            'component' => 'Magento_Ui/js/form/element/date',
            'dataType' => 'string',
            'label' => __('Pickup Date'),
            'formElement' => 'date',
            'validation' => ['required-entry' => true],
            'value' => $this->getDateTime(),
            'options' => [
                'minDate' => $this->getAvailableFrom(),
                'dateFormat' => 'dd-MMM-yyyy',
            ],

        ]);
        $elements['pickup_comments'] = $this->addFieldToLayout('pickup_comments', [
            'dataType' => 'text',
            'formElement' => 'textarea',
            'label' => __('Pickup Note'),
            'placeholder' => __('Pickup Note')
        ]);
        $elements['pickup_time'] = $this->addFieldToLayout('pickup_time', [
            'dataType' => 'text',
            'formElement' => 'text',
            'label' => __('Pickup Time'),
            'value' => $this->checkoutHelper->getPickupTimeContent(),
            'disabled' => true
        ]);

        return $elements;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAddressAttributes()
    {
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection('customer_address', 'customer_register_address');

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
            if ($code == 'prefix') {
                $elements[$code]['dataType'] = 'select';
                $elements[$code]['formElement'] = 'select';
                $elements[$code]['sortOrder'] = 0;
                $elements[$code]['options'][] = ['value' => '', 'label' => __('Salutation')];
                $elements[$code]['options'][] = ['value' => 'Mr', 'label' => __('Mr.')];
                $elements[$code]['options'][] = ['value' => 'Mrs', 'label' => __('Mrs.')];
                $elements[$code]['options'][] = ['value' => 'Mdm', 'label' => __('Mdm.')];
                $elements[$code]['options'][] = ['value' => 'Ms', 'label' => __('Ms.')];
                $elements[$code]['options'][] = ['value' => 'Dr', 'label' => __('Dr.')];
                $elements[$code]['value'] = str_replace('.', '', $this->checkoutSession->getQuote()->getShippingAddress()->getPrefix());
            }
        }
        $elements['billing_email'] = $this->addFieldToLayout('billing_email', [
            'dataType' => 'text',
            'formElement' => 'input',
            'label' => __('Email Address'),
            'sortOrder' => 45,
            'validation' => [
                'max_text_length' => 255,
                'min_text_length' => 1,
                'required-entry' => true,
                'validate-email' => true

            ],
            'value' => $this->checkoutSession->getQuote()->getShippingAddress()->getEmail(),
        ]);
        $elements['floor']=$this->addFieldToLayout('floor', [
            'label' => __('Floor/Unit'),
            'dataType' => 'text',
            'formElement' => 'input',
            'sortOrder' => 25,
            'validation' => [
                'required-entry' => true
            ],
            'value' => $this->getQuote()->getData('billing_floor')
        ]);

        $elements['building'] = $this->addFieldToLayout('building', [
            'formElement' => 'input',
            'dataType' => 'text',
            'label' => __('Building Name'),
            'sortOrder' => 30,
            'validation' => [
                'required-entry' => false
            ],
            'value' => $this->getQuote()->getData('billing_building'),
        ]);

        return $elements;
    }

    /**
     * @param string $customAttributeCode
     * @param array $customField
     * @return array
     */
    protected function addFieldToLayout($customAttributeCode = 'custom_field', $customField = [])
    {
        return array_merge([
            'component' => 'Magento_Ui/js/form/element/date',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/date'
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
            'label' => 'Custom Attribute',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'validation' => [
                'required-entry' => true
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'value' => ''
        ], $customField);
    }

    /**
     * @param $elements
     * @return array
     */
    protected function getDeliveryScheduleComponent($elements)
    {
        $components = [
            'component' => 'uiComponent',
            'displayArea' => 'delivery-extra-information',
            'children' => $this->merge->merge(
                $elements,
                'checkoutProvider',
                'delivery-extra-information',
                [
                    'pickup_location' => ['sortOrder' => 1],
                    'pickup_date' => ['sortOrder'=>30],
                    'pickup_comments' => ['sortOrder' => 15],
                    'pickup_time' => ['sortOrder' => 50]
                ]
            )
        ];

        return $components;
    }


    /**
     * @param $elements
     * @return array
     */
    protected function getCustomShippingAddressComponent($elements)
    {
        $components = [
            'component' => 'uiComponent',
            'displayArea' => 'additional-fieldsets',
            'children' => $this->merge->merge(
                $elements,
                'checkoutProvider',
                'shipping-address-fieldset',
                [
                    'prefix' => ['sortOrder'=>1, 'label' => false],
                    'firstname' => [
                        'sortOrder' => 10
                    ],
                    'lastname' => [
                        'sortOrder' => 15
                    ],
                    'street'=> ['sortOrder'=>20],
                    'floor' => ['sortOrder'=>25],
                    'building' => ['sortOrder'=>30],
                    'country_id' => ['sortOrder'=>35],
                    'postcode' => ['sortOrder'=>40],
                    'billing_email' => ['sortOrder'=>45],
                    'telephone' => ['sortOrder'=>50]
                ]
            )
        ];

        return $components;
    }


    /**
     * Prepare billing address field for shipping step for physical product
     *
     * @param $elements
     * @return array
     */
    public function getCustomBillingAddressComponent($elements)
    {
        $providerName = 'checkoutProvider';

        $components = [
            'component' => 'uiComponent',
            'displayArea' => 'additional-fieldsets',
            'children' => $this->merge->merge(
                $elements,
                $providerName,
                'billingAddress',
                [
                    'firstname' => [
                        'sortOrder' => 10
                    ],
                    'lastname' => [
                        'sortOrder' => 15
                    ],
                    'prefix' => [
                        'label' => false
                    ]
                ]
            ),
        ];

        return $components;
    }


    /**
     * @return array
     */
    private function getSmartGiftAttributes()
    {
        $elements = [];
        //$elements['gift-confirmation'] = $this->addFieldToLayout('gift-confirmation', [
        //    'component' => 'Magento_Ui/js/form/element/single-checkbox',
        //    'dataType' => 'string',
        //    'label' => __('Want a Gift Card'),
        //    'config' => [
        //    ],
        //    'formElement' => 'checkbox'
        //]);

        return $elements;
    }

    /**
     * @param $elements
     * @return array
     */
    private function getSmartGiftComponent($elements)
    {
        $providerName = 'checkoutProvider';

        $components = [
            'component' => 'uiComponent',
            'displayArea' => 'smart-gift',
            'children' => $this->merge->merge(
                $elements,
                $providerName,
                'smart-gift',
                []
            ),
        ];

        return $components;
    }

    /**
     * @return false|string[]
     */
    public function getSpecialDisableDate()
    {
        $arrSpecialDate = $this->specialDate->getSpecialDisableDate();
        foreach ($arrSpecialDate as $key => $item) {
            $arrSpecialDate[$key] = strtotime($item);
        }
        return $arrSpecialDate;
    }
}
