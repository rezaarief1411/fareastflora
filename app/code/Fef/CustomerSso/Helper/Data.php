<?php

namespace Fef\CustomerSso\Helper;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Store\Model\StoreManagerInterface;
use Fef\CustomShipping\Helper\Data as CustomHelper;
use Fef\CustomShipping\Model\FefTokenFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const STR_MEMBERSHIP_URL = 'memberships/';

    private $request;
    private $helper;
    private $modelFefTokenFactory;
    public $storemanager;
    private $customerFactory;
    private $customerRepository;
    private $customerSession;
    private $resourceConnection;
    private $eavAttribute;
    private $indexerInterfaceFactory;
    public $encryptInterface;
    public $customerInterface;
    private $resultFactory;

    public function __construct(
        Http $request,
        CustomHelper $helper,
        FefTokenFactory $modelFefTokenFactory,
        StoreManagerInterface $storemanager,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        ResourceConnection $resourceConnection,
        Attribute $eavAttribute,
        IndexerInterfaceFactory $indexerInterfaceFactory,
        EncryptorInterface $encryptInterface,
        CustomerInterfaceFactory $customerInterface,
        ResultFactory $resultFactory
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->modelFefTokenFactory = $modelFefTokenFactory;
        $this->storemanager = $storemanager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->eavAttribute = $eavAttribute;
        $this->indexerInterfaceFactory = $indexerInterfaceFactory;
        $this->encryptInterface = $encryptInterface;
        $this->customerInterface = $customerInterface;
        $this->resultFactory = $resultFactory;
    }
    
    public function checkProsellerByEmail($email)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-login.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $baseUrl = $this->helper->getConfig("carriers/custom/base_url");
        $membershipUrl = $baseUrl.self::STR_MEMBERSHIP_URL;
        $getByEmailResponse = $this->helper->setCurl(
            $membershipUrl."byEmail/".$email,
            "GET",
            null,
            1
        );
        $logger->info($getByEmailResponse);
        $respArray = json_decode($getByEmailResponse,1);
        return $respArray;
    }

    public function sendOtp($email)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-register.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $baseUrl = $this->helper->getConfig("carriers/custom/base_url");
        $membershipUrl = $baseUrl.self::STR_MEMBERSHIP_URL;
        $sendOtpParams = array(
            "email" => $email
        );
        $sendOtpResponse = $this->helper->setCurl(
            $membershipUrl."otp/send",
            "POST",
            $sendOtpParams,
            1
        );
        // $logger->info("membershipUrl : $membershipUrl");
        // $logger->info(print_r($sendOtpParams,true));
        $respOtpArray = json_decode($sendOtpResponse,1);
        $logger->info("===================== sendOtp helper =======================");
        // $logger->info(print_r($respOtpArray,true));

        
        if(isset($respOtpArray["status"]) && $respOtpArray["status"]=="success"){
            $resultJson->setData([
                "message" => $respOtpArray["message"], 
                "success" => true
            ]);
        } else{
            // $logger->info(print_r($respOtpArray,true));
            $resultJson->setData([
                "message" => $respOtpArray["message"],
                "success" => false
            ]);
        }
        
        return $resultJson;
    }

    public function validateOtp($email,$otpValue)
    {
        $baseUrl = $this->helper->getConfig("carriers/custom/base_url");
        $membershipUrl = $baseUrl.self::STR_MEMBERSHIP_URL;
        $sendOtpParams = array(
            "email" => $email,
            "otp" => $otpValue,
        );
        $sendOtpResponse = $this->helper->setCurl(
            $membershipUrl."otp/validate",
            "POST",
            $sendOtpParams,
            1
        );
        $respArray = json_decode($sendOtpResponse,1);
        return $respArray;
        
    }

    public function createMembership($postEmail,$resultJson)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-login.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        
        $websiteID = $this->storemanager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()->setWebsiteId($websiteID)->loadByEmail($postEmail);
        
        $customerModel = $this->customerRepository->getById($customer->getId());

        $cattrPhonenumberValue = $customerModel->getCustomAttribute('phone_number');
        $phoneNumber = $cattrPhonenumberValue ? "+65".$cattrPhonenumberValue->getValue() : null;
        
        $customerName = $customer->getFirstname();
        if($customer->getMiddlename()!=NULL){
            $customerName.=" ".$customer->getMiddlename();
        }
        if($customer->getLastname()!=NULL){
            $customerName.=" ".$customer->getLastname();
        }

        $baseUrl = $this->helper->getConfig("carriers/custom/base_url");
        $membershipUrl = $baseUrl.self::STR_MEMBERSHIP_URL;
        $creatdParams = array(
            "name" => $customerName,
            "email" => $postEmail,
            "phoneNumber" => $phoneNumber
        );
        $createResponse = $this->helper->setCurl(
            $membershipUrl,
            "POST",
            $creatdParams,
            1
        );
        $respCreate = json_decode($createResponse,1);
        $logger->info("===================== createMembership =======================");
        $logger->info(print_r($respCreate,true));

        if(isset($respCreate["status"]) && $respCreate["status"]=="success"){
            
            $arrAttribute = $this->getAttributeList($respCreate["data"]);
            $this->updateCustomerAttribute($arrAttribute,$postEmail);

            $resultJson->setData([
                "message" => "Customer created successfully to proseller",
                "success" => true
            ]);
        } else {
            if(isset($respCreate["status"]) && $respCreate["status"]=="fail"){
                $resultJson->setData([
                    "message" => $respCreate["data"]["undefined"],
                    "success" => false
                ]);
            }else{
                $resultJson->setData([
                    "message" => $respCreate["message"],
                    "success" => false
                ]);
            }
        }
        return $resultJson;
    }

    public function getMemberProsellerId($postEmail)
    {
        $customer = $this->getCustomerByEmail($postEmail);
        $customerModel = $this->customerRepository->getById($customer->getId());
        
        $cattrValue = $customerModel->getCustomAttribute('proseller_member_id');
        $memberProsellerId = $cattrValue ? $cattrValue->getValue() : null;
    }
    
    public function updateCustomerAttribute($arrAttribute,$email)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-login.log');
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

    public function getCustomerByEmail($email)
    {
        $websiteID = $this->storemanager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()->setWebsiteId($websiteID)->loadByEmail($email);
        return $customer;
    }

    public function createCustomer($params)
    {
        
        $storeId = $this->storemanager->getStore()->getId();        
        $websiteId = $this->storemanager->getStore($storeId)->getWebsiteId();
        $customer = $this->customerInterface->create();

        $email = $params["email"];
        $arrFullName = explode(" ",$params["name"]) ;
        $firstName = $arrFullName[0];
        $lastName = $firstName;
        if(isset($arrFullName[1]) && $arrFullName[1] != ""){
            $lastName = $arrFullName[1];
        }
        $customer->setWebsiteId($websiteId);
        $customer->setFirstname($firstName);
        $customer->setLastname($lastName);
        $customer->setEmail($email);
        $hashedPassword = $this->encryptInterface->getHash($email, true);
        $this->customerRepository->save($customer, $hashedPassword);

        if(isset($params["deliveryAddress"])){
            $this->setCustomerAddress($params);
        }
        $arrAttribute = $this->getAttributeList($params);
        // $logger->info("===================== getAttributeList =======================");
        // $logger->info(print_r($arrAttribute,true));

        // $logger->info("===================== updateCustomerAttribute in createCustomer =======================");
        $this->updateCustomerAttribute($arrAttribute, $email);
        $this->reindexCustomer();
    }

    public function reindexCustomer(){
        $this->getIndexer('customer_grid')->reindexAll();
    }

    /**
     * @param string $indexerId
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    public function getIndexer($indexerId)
    {
        return $this->indexerInterfaceFactory->create()->load($indexerId);
    }

    public function setCustomerAddress($params)
    {
        $email = $params["email"];

        $customer = $this->getCustomerByEmail($email);

        $arrFullName = explode(" ",$params["name"]) ;
        $firstName = $arrFullName[0];
        $lastName = $firstName;
        if(isset($arrFullName[1]) && $arrFullName[1] != ""){
            $lastName = $arrFullName[1];
        }
        $customerId = $customer->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $addresss = $objectManager->get('\Magento\Customer\Model\AddressFactory');
        $address = $addresss->create();

        $postalCode = "";
        if(isset($params["deliveryAddress"]["postalCode"])){
            $postalCode = $params["deliveryAddress"]["postalCode"];
        }

        $postalCode = "";
        if(isset($params["deliveryAddress"]["postalCode"])){
            $postalCode = $params["deliveryAddress"]["postalCode"];
        }

        $city = "";
        if(isset($params["deliveryAddress"]["city"])){
            $city = $params["deliveryAddress"]["city"];
        }

        $streetName = "";
        if(isset($params["deliveryAddress"]["streetName"])){
            $streetName = $params["deliveryAddress"]["streetName"];
        }

        $address->setCustomerId($customerId)
        ->setFirstname($firstName)
        ->setLastname($lastName)
        ->setPostcode($postalCode)
        ->setCity($city)
        ->setTelephone($params["phoneNumber"])
        ->setStreet($streetName)
        ->setIsDefaultBilling('1')
        ->setIsDefaultShipping('1')
        ->setSaveInAddressBook('1');
        $address->save();
    }

    public function getAttributeList($params)
    {
        $arrAttribute = array(
            array(
                "code"  => "proseller_member_id",
                "table" => "customer_entity_varchar",
                "value" => $params["id"]
            ),
            array(
                "code"  => "phone_number",
                "table" => "customer_entity_varchar",
                "value" => $params["phoneNumber"]
            )
        );
        return $arrAttribute;
    }

    public function updateMembership($dataUpdate,$memberId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP.'/var/log/customer-update.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        
        $baseUrl = $this->helper->getConfig("carriers/custom/base_url");
        $membershipUrl = $baseUrl.self::STR_MEMBERSHIP_URL.$memberId;

        $updateResponse = $this->helper->setCurl(
            $membershipUrl,
            "PATCH",
            $dataUpdate,
            1
        );
        $respUpdate = json_decode($updateResponse,1);
        $logger->info("===================== updateMembership =======================");
        $logger->info(print_r($respUpdate,true));

        $resultData = [];
        if(isset($respUpdate["status"]) && $respUpdate["status"]=="success"){
            $resultData = [
                "message" => "Customer updated successfully to proseller",
                "success" => true
            ];
        } else {
            if(isset($respUpdate["status"]) && $respUpdate["status"]=="fail"){
                if(isset($respUpdate["data"])){
                    foreach ($respUpdate["data"] as $key => $value) {
                        $resultData = [
                            "message" => $respUpdate["data"][$key],
                            "success" => false
                        ];
                    }
                }
            }else{
                $resultData = [
                    "message" => $respUpdate["message"],
                    "success" => false
                ];
            }
        }
        return $resultData;
    }
}
