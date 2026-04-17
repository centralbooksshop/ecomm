<?php
namespace SchoolZone\Customer\Controller\Index;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\User\Model\UserFactory;


class Search extends \Magento\Framework\App\Action\Action
{
	protected $_storeManager;
	protected $userCollectionFactory;
	protected $resultRedirectFactory;
	protected $_resource;
	protected $logger;
    protected $userFactory;
	protected $encryptor;
	public function __construct(
		\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolCollection,
		\Magento\Framework\Controller\Result\Redirect $resultRedirectFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		 EncryptorInterface $encryptor,
		\Psr\Log\LoggerInterface $logger,
		 \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
		 UserFactory $userFactory,
		 \Magento\Framework\App\ResourceConnection $resource,
		\Magento\Framework\App\Action\Context $context
		)
	{
		$this->schoolCollection = $schoolCollection;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->_storeManager = $storeManager;
		$this->encryptor = $encryptor;
		$this->logger = $logger;
		$this->userFactory = $userFactory;
		$this->_resource = $resource;
		$this->userCollectionFactory = $userCollectionFactory;
		parent::__construct($context);
	}

	public function execute()
	{
		$_SESSION["admin_token"] = '';
		$_SESSION["school_name"] = '';
		$_SESSION["user_name"] = '';
		$username = '';
		$password = '';

		$username = $this->getRequest()->getPost('username');
		$password = $this->getRequest()->getPost('password');
		
		$admin_collection = $this->userCollectionFactory->create();
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
		$adminUser = $this->userFactory->create();
		$adminUser->loadByUsername($username);
        $user = 0;
		if (!$adminUser->getId()) {
			$_SESSION["admin_token"] ='';
			$_SESSION["school_name"] = '';
			echo "invalied_user";
			exit;
		}

		$hashedPassword = $adminUser->getPassword();
		if (!$this->encryptor->validateHash($password, $hashedPassword)) {
			$_SESSION["admin_token"] ='';
			$_SESSION["school_name"] = '';
			echo "invalied_user";
			exit;
		}
       else{   
		    $user = 1;	
	        unset($_SESSION['school_name']);
			unset($_SESSION['user_name']);
			$_SESSION["school_name"] = $adminUser->getSchool();
			$_SESSION["user_name"] = $adminUser->getUsername();
	   }
		if($user == 1) {
			// $_SESSION["admin_token"] = $response;
			if((isset($_SESSION["school_name"])) && ($_SESSION["school_name"] != '')){
				$this->logger->info('session school_name: '.$_SESSION["school_name"]);
				$schools_coll = $this->schoolCollection->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('school_name',['in'=>$_SESSION["school_name"]]);
				//$schools_coll->getSelect()->__toString();
                //echo '<pre>';print_r($schools_coll->getData());die;
        		if($schools_coll->getFirstItem()->getData('id')){
					$url_page_path = 'schoolzone_customer/index/Display';
					$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
					$url = $baseUrl.$url_page_path;
					echo $url;	
				}else{
					echo "invalied_school_user";
				}

			}else{
				echo "invalied_school_user";
			}
		} else{
			$_SESSION["admin_token"] ='';
			$_SESSION["school_name"] = '';
			echo "invalied_user";
		}
     //echo 'ravi'.$_SESSION["school_name"];die;
	}

	protected function deleteUserRow($username) {
       
	$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
	$tbl_log = $connection->getTableName('oauth_token_request_log');
    $whereConditions = [
            $connection->quoteInto('user_name = ?', $username),
     ];
     $deleteRows = $connection->delete($tbl_log, $whereConditions);
      
	}

	protected function checkUserExist($username) {
	$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
	$tbl_log = $connection->getTableName('oauth_token_request_log');
    $sql= "SELECT log_id FROM " .$tbl_log. " WHERE user_name = ? AND failures_count >=3";
    return $connection->fetchOne($sql, [$username]); 
	}
	
}
