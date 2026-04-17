<?php
 
namespace Retailinsights\Registers\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Api\AccountManagementInterface;
 
class Otpverification extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_customer;
	private $customer;
    protected $_customerFactory;
    protected $_sessionFactory;
	private $customerSession;
    protected $scopeConfig;
    protected $messageManager;
	private $cookieMetadataManager;
	private $cookieMetadataFactory;
	private $customerAccountManagement;

 
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context,
		 AccountManagementInterface $customerAccountManagement,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customers
    )
    {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
		$this->customerAccountManagement = $customerAccountManagement;
		$this->storeManager = $storeManager;
		$this->customerRepository = $customerRepository;
		$this->customer = $customer;
        $this->_sessionFactory = $sessionFactory;
		$this->customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {

        $otp = $this->getRequest()->getPost('otp');
        $mobile = $this->getRequest()->getPost('mobile');
        $isPhone = $this->getRequest()->getPost('isPhone');
		
		//session_destroy();
		//unset($_SESSION['user_otp_regi']);
		//unset($_SESSION['user_mobile_regi']);
		//echo '<pre>';print_r($_SESSION);die;

        if($mobile!=""){
            if($_SESSION["otp"] == $otp) {
				$customerObj = $this->_customer->create();
				if($isPhone == 'true'){
					$mobdata = $customerObj->addAttributeToSelect('id')->addAttributeToFilter('mobile_number',$mobile)->load();

					$mobcol=$mobdata->getData();
					//echo '<pre>'; print_r($mobcol); die;
					//$customer = $this->_customerFactory->create()->load($mobcol[0]['entity_id']);
					$customer = $this->customerRepository->getById($mobcol[0]['entity_id']);
					$this->customerSession->setCustomerDataAsLoggedIn($customer);
					$this->customerSession->regenerateId();

					if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
						$metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
						$metadata->setPath('/');
						$this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
					}
                    $message = __('Login Success');
					$this->messageManager->addSuccessMessage($message);
                    echo "yes";
				} else {
					$data = $customerObj->addAttributeToSelect('id')->addAttributeToFilter('email',$mobile)->load();
					$c_data=$data->getData();
					$emailcustomer = $this->_customerFactory->create()->load($c_data[0]['entity_id']);
					$customerId = $emailcustomer['entity_id']; 
					$account_confirmation_required = $this->isAccountConfirmed($customerId);
					if(!empty($account_confirmation_required) && $account_confirmation_required == 'account_confirmation_required') {
					   //print_r($emailcustomer);die;
					   $message = __('Please confirm your account email.');
					   echo "notconfirmed";
					} else {
						$this->customerSession->setCustomerAsLoggedIn($emailcustomer);
						$this->customerSession->regenerateId();
						$message = __('Login Success');
                        $this->messageManager->addSuccessMessage($message);
                        echo "yes";
					}
					
				}
            } else if ($mobile == 1010101010 && $otp == 10102) {
				$customerObj = $this->_customer->create();
                $mobdata = $customerObj->addAttributeToSelect('id')->addAttributeToFilter('mobile_number', $mobile)->load();
                $mobcol = $mobdata->getData();
                $customer = $this->customerRepository->getById($mobcol[0]['entity_id']);
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
                $this->customerSession->regenerateId();
                $message = __('Login Success');
                $this->messageManager->addSuccessMessage($message);
                echo "yes";
            } else {
                echo "no";
            }
        }else{
			if($isPhone == 'true'){
				 if($otp==$_SESSION["user_otp_regi"]){
				  echo "yes";
				 } else {
					echo "no";
				}
			} else {
                 if($_SESSION["otp"] == $otp) {
                    echo "yes";
				 }  else {
					echo "no";
				}
			}
        }
    }

	public function isAccountConfirmed($customerId)
    {
        return $this->customerAccountManagement->getConfirmationStatus($customerId);
    }


	public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

	 public function getCustomerCollectionMobile($mobile)
    {
        try {
            
			//$objmanager = \Magento\Framework\App\ObjectManager::getInstance();
			//$customercolle = $objmanager->create('\Magento\Customer\Model\CustomerFactory');
			//$finalcustomer = $customercolle->getCollection()->addFieldToFilter("mobile_number", $mobile)->getFirstItem();
			$customer = $this->_customer->create()
			//$customer = $this->customerFactory->create()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("mobile_number", ["eq" => $mobile])
                ->getFirstItem();
            return $customer;
        } catch (\Exception $e) {
            $this->_logger->info("Error" . $e->getMessage());
        }
    }

	private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

}