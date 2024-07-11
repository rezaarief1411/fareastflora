<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_<modulename>
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Fef\CustomerSso\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;
use Fef\CustomerSso\Helper\Data as CustomerHelper;
use Fef\CustomShipping\Helper\Data;
use Fef\CustomShipping\Model\FefTokenFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;

class RegisterEmail extends \Magento\Framework\App\Action\Action
{

    const STR_MEMBERSHIP_URL = 'memberships/';
    const DEFAULT_COOKIE_LIFETIME = 172800; // 2 days

    /**
     * @var \Magento\Framework\App\Action\Contex
     */
    private $context;
    private $request;
    private $helper;
    private $modelFefTokenFactory;
    private $customerFactory;
    private $customerRepository;
    private $customerSession;
    private $customerHelper;
    private $prosellerId;



    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        Http $request,
        Data $helper,
        FefTokenFactory $modelFefTokenFactory,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        CustomerHelper $customerHelper
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->request = $request;
        $this->helper = $helper;
        $this->modelFefTokenFactory = $modelFefTokenFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerHelper = $customerHelper;
    }
    
    /**
     * @return json
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-register.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $modelFefToken = $this->modelFefTokenFactory->create();

        
        
        try {

            $registerParams = $this->request->getParams();

            // $logger->info("===================== registerParams =======================");
            // $logger->info(print_r($registerParams,true));
            
            $email = $registerParams['email'];
            $otp = $registerParams['register']["otp"];

            if(isset($registerParams["type"]) && isset($registerParams["type"]) == "resend"){
                $resultJson = $this->customerHelper->sendOtp($email);
            }else{
                if($otp != "" && $otp != "0"){
                    // $logger->info("email : $email, otp : $otp");
                    /**
                     * LAST STEP, VALIDATE OTP THAT HAS INPUT IN FRONTEND
                     */
                    $respOtpArray = $this->customerHelper->validateOtp($email,$otp);
                    // $logger->info("===================== validateOtp =======================");
                    // $logger->info(print_r($respOtpArray,true));

                    if(isset($respOtpArray["status"]) && $respOtpArray["status"]=="success"){
                        /**
                         * IF TOKEN IS VALID, THEN : 
                         *  - UPDATE EXISTING TOKEN
                         *  - CREATE MAGENTO CUSTOMER WITH REGISTER FORM DATA
                         *  - REDIRECT TO MY ACCOUNT PAGE
                         */

                        // DIsable check and update token because of use long live token

                        /*
                        $tokenCollection = $this->helper->getTokenCollection();
                        $dataCollection = $tokenCollection->getData();
                        foreach ($dataCollection as $key => $collection) {
                            $id = $collection["id"];
                            $postUpdate = $modelFefToken->load($id);
                            $postUpdate->setToken($respOtpArray["data"]["token"]);
                            $postUpdate->setRefreshToken($respOtpArray["data"]["refreshToken"]);
                            $postUpdate->save();
                        }
                        */
                        // $logger->info("===================== update token done =======================");

                    
                        $this->createCustomerFromRegisterForm($registerParams);
                        // $logger->info("===================== createCustomerFromRegisterForm =======================");

                        $customerRepo = $this->customerRepository->get($email); 
                        $customer = $this->customerFactory->create()->load($customerRepo->getId());
                        $this->customerSession->setCustomerAsLoggedIn($customer);
                        
                        $_SESSION["prosellerId"] = '';
                        
                        $resultJson->setData([
                            "message" => "OTP has been successfully validated", 
                            "success" => true
                        ]);
                        
                    } else {
                        $resultJson->setData([
                            "message" => $respOtpArray["data"]["message"], 
                            "success" => false
                        ]);
                    }
                }else{

                    /**
                     * FIRST CHECK IF EMAIL IS EXIST OR NOT IN MAGENTO 
                     * IF EMAIL EXIST IN MAGENTO THEN RETURN ERROR EMAIL IS EXIST
                     */

                    $customer = $this->customerHelper->getCustomerByEmail($email);

                    if($customer->getId() != null){
                        $resultJson->setData([
                            "message" => "Email is exists",
                            "success" => false
                        ]);

                    } else {

                        /**
                         * EMAIL NOT EXIST IN MAGENTO AND EXIST IN PROSELLER :
                         * - CREATE MAGENTO CUSTOMER WITH PROSELLER ID INCLUDED
                         */

                        $respArray = $this->customerHelper->checkProsellerByEmail($email);

                        $logger->info("===================== checkProsellerByEmail =======================");
                        $logger->info(print_r($respArray,true));
                        
                        if(isset($respArray["status"]) && $respArray["status"]=="success"){
                            
                            /**
                             * IF EMAIL EXIST IN PROSELLER, THEN :
                             * - CREATE MAGENTO CUSTOMER
                             * - RETURN ERROR EMAIL IS EXIST
                             */

                            // CREATE CUSTOMER IN MAGENTO
                            $this->customerHelper->createCustomer($respArray["data"]);

                            $resultJson->setData([
                                "message" => "Email is exists",
                                "success" => false
                            ]);

                        }else{

                            /**
                             * IF EMAIL NOT EXIST IN MAGENTO AND EMAIL NOT EXIST IN PROSELLER, THEN :
                             * - CREATE PROSELLER MEMBER
                             * - SEND OTP
                             */
                            // $resultJson = $this->customerHelper->createMembership($email,$resultJson);
                            $arrResult = $this->createMembershipFromRegisterForm($registerParams);
                            $logger->info("===================== createMembershipFromRegisterForm =======================");
                            $logger->info(print_r($arrResult,true));

                            if($arrResult["success"]==1){
                                $resultJson = $this->customerHelper->sendOtp($email);
                            } else {
                                $resultJson->setData($arrResult);
                            }
                        }
                    }
                }
            }

        } catch (\Exception $ex) {
            $resultJson->setData([
                "message" => ($ex->getMessage()), 
                "success" => false
            ]);
        }
        

        return $resultJson;
    }

    private function createMembershipFromRegisterForm($registerParams)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-register.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $arrResult = [];
        
        $customerName = $registerParams["firstname"]." ".$registerParams["lastname"];
        $phoneNumber = "+".$registerParams["contact_number"].$registerParams["telephone"];
        $email = $registerParams["email"];

        $baseUrl = $this->helper->getConfig("carriers/custom/base_url");
        $membershipUrl = $baseUrl.self::STR_MEMBERSHIP_URL;
        $createdParams = array(
            "name" => $customerName,
            "email" => $email,
            "phoneNumber" => $phoneNumber
        );
        $createResponse = $this->helper->setCurl(
            $membershipUrl,
            "POST",
            $createdParams,
            1
        );

        $respCreate = json_decode($createResponse,1);
        // $logger->info("===================== createMembershipFromRegisterForm =======================");
        // $logger->info(print_r($respCreate,true));

        if(isset($respCreate["status"]) && $respCreate["status"]=="success"){
            
            $_SESSION["prosellerId"] = $respCreate["data"]["id"];
            
            // $logger->info("ID : ".$_SESSION["prosellerId"]);

            $arrAttribute = $this->getAttrList($respCreate["data"]["phoneNumber"]);
            
            $this->customerHelper->updateCustomerAttribute($arrAttribute,$email);

            $arrResult = [
                "message" => "Customer created successfully to proseller",
                "success" => true
            ];
        } else {
            if(!isset($respCreate["message"])){
                $arrResult = [
                    "message" => $respCreate["data"]["message"],
                    "success" => false
                ];
            } else {
                $arrResult = [
                    "message" => $respCreate["message"],
                    "success" => false
                ];
            }
            
        }
        return $arrResult;
    }

    private function createCustomerFromRegisterForm($params)
    {
        
        $storeId = $this->customerHelper->storemanager->getStore()->getId();        
        $websiteId = $this->customerHelper->storemanager->getStore($storeId)->getWebsiteId();
        $customer = $this->customerHelper->customerInterface->create();
        $phoneNumber = "+".$params["contact_number"].$params["telephone"];

        $email = $params["email"];
        $firstName = $params["firstname"];
        $lastName = $params["lastname"];

        $customer->setWebsiteId($websiteId);
        $customer->setFirstname($firstName);
        $customer->setLastname($lastName);
        $customer->setEmail($email);
        $hashedPassword = $this->customerHelper->encryptInterface->getHash($email, true);
        $this->customerRepository->save($customer, $hashedPassword);

        if(isset($params["deliveryAddress"])){
            $this->setCustomerAddressFromRegisterForm($phoneNumber);
        }
        
        $arrAttribute = $this->getAttrList($phoneNumber);
        $this->customerHelper->updateCustomerAttribute($arrAttribute, $email);
        $this->customerHelper->reindexCustomer();
    }

    
    public function getAttrList($phoneNumber)
    {
        // $phoneNumber = "+".$params["contact_number"].$params["telephone"];

        // $prosellerId = $this->getCookie("prosellerId");
        $prosellerId = $_SESSION["prosellerId"];

        

        $arrAttribute = array(
            array(
                "code"  => "proseller_member_id",
                "table" => "customer_entity_varchar",
                "value" => $prosellerId
            ),
            array(
                "code"  => "phone_number",
                "table" => "customer_entity_varchar",
                "value" => $phoneNumber
            )
        );
        return $arrAttribute;
    }

    public function setCustomerAddressFromRegisterForm($params)
    {
        $email = $params["email"];

        $customer = $this->customerHelper->getCustomerByEmail($email);

        $firstName = $params["firstname"];
        $lastName = $params["lastname"];
        $phoneNumber = "+".$params["contact_number"].$params["telephone"];

        $customerId = $customer->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $addresss = $objectManager->get('\Magento\Customer\Model\AddressFactory');
        $address = $addresss->create();
        $address->setCustomerId($customerId)
        ->setFirstname($firstName)
        ->setLastname($lastName)
        ->setPostcode($params["postcode"])
        ->setCity($params["city"])
        ->setTelephone($phoneNumber)
        ->setStreet($params["street"][0])
        ->setIsDefaultBilling('1')
        ->setIsDefaultShipping('1')
        ->setSaveInAddressBook('1');
        $address->save();
    }

    private function setCookie($name, $value)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cookieManager = $objectManager->get("\Magento\Framework\Stdlib\CookieManagerInterface");
        $cookieMetadataFactory = $objectManager->get("\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory");
        $sessionManager = $objectManager->get("\Magento\Framework\Session\SessionManagerInterface");
        
        $metadata = $cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::DEFAULT_COOKIE_LIFETIME)
            ->setPath($sessionManager->getCookiePath())
            ->setDomain($sessionManager->getCookieDomain());
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $cookieManager->setPublicCookie(
            $name,
            $value,
            $metadata
        );
    }

    public function getCookie($name)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cookieManager = $objectManager->get("\Magento\Framework\Stdlib\CookieManagerInterface");
        $value = $cookieManager->getCookie($name);

        return $value;
    }

    public function updateAttribute($arrAttribute,$email)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-register.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info(print_r($arrAttribute,true));

        foreach ($arrAttribute as $key => $val) {
            $tableData = [];
            $customer = $this->getCustomerByEmail($email);
            $customerId = $customer->getId();
            $attributeId = $this->eavAttribute->getIdByCode('customer', $arrAttribute[$key]["code"]);
            // $logger->info("attributeId : $attributeId -> ".$arrAttribute[$key]["code"]);
            if($attributeId==null){
                $failedFlag = 1;
            }else{
                
                if($customerId==null){
                    
                }else{
                    $connection = $this->resourceConnection->getConnection();
                    $table = $connection->getTableName($arrAttribute[$key]["table"]);
                    $query = "SELECT `value_id`, `value` FROM " . $table." WHERE entity_id = ".$customerId." AND attribute_id = $attributeId";
                    // $logger->info("query select -> ".$query);
                    $valueArr = $connection->fetchAll($query);
                    if(empty($valueArr)){
                        $tableColumn = ['attribute_id', 'entity_id', 'value'];
                        $tableData[] = [$attributeId, $customerId, $arrAttribute[$key]["value"]];
                        // $logger->info(print_r($tableData,true));
                        $connection->insertArray($table, $tableColumn, $tableData);
                    } else {
                        $valueId = $valueArr[0]["value_id"];
                        $prosellerId = $valueArr[0]["value"];
                        // if($prosellerId != $dataValue){
                            $query = "UPDATE `" . $table . "` SET `value`= '".$arrAttribute[$key]["value"]."' WHERE value_id = ".$valueId;
                            // $logger->info("query update -> ".$query);
                            $connection->query($query);
                        // }
                    }
                }            
            }
        }
    }

    public function deleteCookie($name)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cookieManager = $objectManager->get("\Magento\Framework\Stdlib\CookieManagerInterface");
        $cookieMetadataFactory = $objectManager->get("\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory");
        $sessionManager = $objectManager->get("\Magento\Framework\Session\SessionManagerInterface");

        $cookieManager->deleteCookie(
            $name,
            $cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($sessionManager->getCookiePath())
                ->setDomain($sessionManager->getCookieDomain())
        );
    }

}