<?php
namespace SchoolZone\Search\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\CacheInterface;

class Search extends \Magento\Framework\App\Action\Action
{
	protected $cache;
	protected $filesystem;
	protected $directoryList;


	protected $_pageFactory;
	protected $_orderCollectionFactory;
	protected $csvProcessor;

	protected $storeManagerInterface;
	protected $_categoryCollection;
	protected $_storeManager;
	protected $jsonFactory;
	protected $resultRedirectFactory;
	protected $resultFactory;
	protected $redirect;

	protected $_resultLayoutFactory;
	protected $transportBuilder;
	protected $eavAttribute;
    protected $eavConfig;
    protected $postFactory;
    protected $postlistFactory;
    protected $_customerRepositoryInterface;
    protected $addressRepositoryInterface;
    private $order;

	public function __construct(
		 CacheInterface $cache,
		\Magento\Framework\File\Csv $csvProcessor,
    \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
    \Magento\Framework\Filesystem $filesystem,

		\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\SchoolZone\Search\Model\NotifyReportFactory $notifyReport,
		\Magento\Sales\Model\Order $order,
		\Magento\Customer\Api\AddressRepositoryInterface  $addressRepositoryInterface,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\SchoolZone\Search\Model\PostFactory $postFactory,
		\SchoolZone\Search\Model\PostlistFactory $postlistFactory,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\Eav\Model\Attribute $eavAttribute,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Framework\App\Response\RedirectInterface $redirect,


		\Magento\Framework\Controller\Result\Redirect $resultRedirectFactory,
		 \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
		\Magento\Store\Model\StoreManagerInterface $StoreManagerInterface,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		 $this->cache = $cache;
		$this->filesystem = $filesystem;  
         $this->directoryList = $directoryList;

		$this->csvProcessor = $csvProcessor;
		$this->_fileFactory = $fileFactory;
		$this->_productRepository = $productRepository;
		$this->notifyReport = $notifyReport;
		$this->order = $order;
		$this->addressRepositoryInterface = $addressRepositoryInterface;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->resultFactory = $resultFactory;
		$this->redirect = $redirect;

		$this->postlistFactory = $postlistFactory;
		$this->postFactory = $postFactory;
		$this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
		$this->transportBuilder = $transportBuilder;
		$this->_resultLayoutFactory = $resultLayoutFactory;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->jsonFactory = $jsonFactory;
		$this->storeManagerInterface = $StoreManagerInterface;
		$this->_categoryCollection = $categoryCollection;
        	$this->_storeManager = $storeManager;
		$this->_pageFactory = $pageFactory;
		parent::__construct($context);
	}

	public function execute()
	{

        $name = $type= $location= $category_key='';
        $category_key = $this->getRequest()->getPost('category_key');
		$name = $this->getRequest()->getPost('name');
		$type = $this->getRequest()->getPost('type');
		$location = $this->getRequest()->getPost('location');
		$result = $this->jsonFactory->create();

		if($type=='category_key'){
			$collection_cat = $this->_categoryCollection->create()
	        ->addAttributeToSelect('*')
	        ->setStore($this->_storeManager->getStore())
	        ->addAttributeToFilter('is_active','1')
	        ->addAttributeToFilter('entity_id',$category_key);

	        $url_path = '';
	        foreach ($collection_cat as $value) {
		        $url_path = $value['url_path'];
	        }
	        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();

	        $url =$baseUrl.$url_path.'.html';
		       

		        // $resultRedirect = $this->resultRedirectFactory->create();
          //       $redirectLink = "http://cbo-dev2.theretailinsights.co/schools/select-your-school/aksharavaagdevi-international-school-secundera/lkg.html"; 
          //       $resultRedirect->setUrl($redirectLink);
                echo $url;

		}

		if($type=='register_school'){
			$url_page_path = 'register-school';
			$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
			$url = $baseUrl.$url_page_path;
			echo $url;
		}	
		
		if($type=='notify_school'){
			$notify_name = $this->getRequest()->getPost('notify_name');
			$notify_phone = $this->getRequest()->getPost('notify_phone');
			$notify_email = $this->getRequest()->getPost('notify_email');
			$notify_schoolname = $this->getRequest()->getPost('notify_schoolname');
			$notify_schooladdress = $this->getRequest()->getPost('notify_schooladdress');
			$notify_message = $this->getRequest()->getPost('notify_message');


			$from = 'help@centralbooksonline.com';

  			$templateVars = array(
                'notify_name'=> $notify_name,
                'notify_phone'=> $notify_phone,
                'notify_email' => $notify_email,
                'notify_schoolname'=> $notify_schoolname,
				'notify_schooladdress'=>$notify_schooladdress,
                'notify_message'=>$notify_message,
			);
			
			$response = $this->saveNotifyMe($templateVars);
			if($response['message'] == 'saved'){
				$newTemplateVars = array(
					'id' => $response['id'],
					'notify_name'=> $notify_name,
					'notify_phone'=> $notify_phone,
					'notify_email' => $notify_email,
					'notify_schoolname'=> $notify_schoolname,
					'notify_schooladdress'=>$notify_schooladdress,
					'notify_message'=>$notify_message,
				);	
				$reply = $this->sendEmail($newTemplateVars, $notify_email, $from);
				echo 'yes';
			}elseif($response['message'] == 'not_saved'){
				echo 'no';
			}

		}	

		// $name = 'AksharaVaagdevi International School-Secundera';
		if($type=='search'){
			if(trim($name) == ''){
				return $result->setData('');	
			}

				$cacheId = 'IdForCachingPurposes';
				$collectionData='';
        if ($cacheData = $this->cache->load($cacheId)) {
            $collectionData = unserialize($cacheData);
        }else{
					$collectionTest = $this->postFactory->create()->getCollection();
					$collectionTest->addFieldToFilter('school_status','1');

					$collectionData = json_encode($collectionTest->getData());
	        $this->cache->save(serialize($collectionData), $cacheId, [], 86400);
        }

      $collectionData = json_decode($collectionData, TRUE);
      $schools =array();
      foreach ($collectionData as $key => $dataval) {
      	$nameLower = strtolower($dataval['school_name_text']);
      	$searchLower = strtolower($name);
      	if(strstr($nameLower,$searchLower)){
      		$schools[] = $dataval;
      	}
      }

			// $collection = $this->postFactory->create()->getCollection();
			// $attributeCollection = $this->eavAttribute->getCollection();

			// $category_parent = $collection->addFieldToFilter('school_name_text', array('like' => '%'.$name.'%'));

			$entity_id = '';
			$category_name = '';
			$html='';
		    foreach ($schools as $data){
		    	$html.="<span class='schoolRes'><input type='text' class='responce_hint' value='".htmlspecialchars($data['school_name_text'], ENT_QUOTES)."'>
		    			<input type='hidden' class='school_type' value='".$data['school_type']."'>
		    			<input type='hidden' class='entity_id' value='".$data['id']."'>
		    			<input type='hidden' class='school_name' value='".$data['school_name']."'>
		    			<input type='hidden' class='school_board' value='".$data['school_board']."'>
		    			<input type='hidden' class='school_city' value='".$data['school_city']."'>
		    			<input type='hidden' class='dependent_field' value='".$data['dependent_field']."'>
		    			<input type='hidden' class='username' value='".$data['username']."'>
		    			<input type='hidden' class='password' value='".$data['password']."'>
		    			<input type='hidden' class='description' value='".htmlspecialchars($data['description'], ENT_QUOTES)."'></span>";
		    	
		    }
		    return $result->setData($html);
			echo $category_name;
		}

	   if($type=='board'){
		   	$entity_id = $this->getRequest()->getPost('entity_id');
		   	$id = $this->getRequest()->getPost('id');

			$collection = $this->postFactory->create()->getCollection();


			$category_parent = $collection->addFieldToFilter('school_name_text', $id);
			$category_parent->getSelect()->group('school_board');

			$attribute = $this->eavConfig->getAttribute('catalog_product', 'board');
		    $options = $attribute->getSource()->getAllOptions();

			$html="<option class='option_hint' value=''>--select--</option>";
					foreach ($category_parent as $value) {
							$id = $value['school_board'];
							
						    
						    foreach ($options as $value) {
						    	if($value['value'] == $id){
						    		$html.="<option class='option_hint' value='".$value['value']."'>".$value['label']."</option>";
						    	}
						    }
					}
			return $result->setData($html);
	   }
	    if($location=='location'){
	    	$entity_id = $this->getRequest()->getPost('entity_id');
	    	$id = $this->getRequest()->getPost('id');

			$collection = $this->postFactory->create()->getCollection();

			$category_parent = $collection->addFieldToFilter('school_name_text', $id)->addFieldToFilter('school_board', $entity_id);
			$category_parent->getSelect()->group('school_city');

			$attribute = $this->eavConfig->getAttribute('catalog_product', 'cities');
    		$options = $attribute->getSource()->getAllOptions();

		

			$html="<option class='option_hint' value=''>--select--</option>";
			
			foreach ($category_parent as $value_school) {
					foreach ($options as $value) {
				    	if($value['value'] == $value_school['school_city']){
				    		$html.="<option class='option_hint' value='".$value['value']."'>".$value['label']."</option>";
				    	}
				    }
			}

	    	 return $result->setData($html);
	    }

	    if($type=='school_name_text'){

				$school_name = $this->getRequest()->getPost('school_name');
				$collection = $this->postFactory->create()->getCollection();

	            $collection->getSelect()->group('school_name_text');

	            foreach ($collection as $value) {
	                if($value['school_name_text'] ==$school_name){

	                	$school_type = $value['school_type'];

		                }
	            }

				echo $school_type;

		 }


		 if($type=='pre_school_search'){
				$school_name = $this->getRequest()->getPost('school_name');
				$collection = $this->postFactory->create()->getCollection();
                $school_type = 0;
                $school_return = '';
	            $collection->getSelect()->group('school_name_text');

	            foreach ($collection as $value) {
	            	if($value['school_name_text'] == $school_name){
	            		$school_type = $value['school_type'];
		                $school_return = $value['school_name_text'];
						$display_bookstore = $value['display_bookstore'];
	            	}
	            		
	            	// similar_text($value['school_name_text'],$school_name,$percentage);
	             //    if(intval($percentage) > 70){
		            //     	$school_type = $value['school_type'];
		            //     	$school_return = $value['school_name_text'];
		            //     }
	            	// }
	            }
				return $result->setData(array("type"=>$school_type,"name"=>$school_return,"displaybookstore"=>$display_bookstore));
		 }


	    if($type=='search_list'){
			$school_name_post = $this->getRequest()->getPost('school_name');
			$username = $this->getRequest()->getPost('username');
			$password = $this->getRequest()->getPost('password');
			$roll_numbers = $this->getRequest()->getPost('roll_numbers');
			$school_type = '';
			$school_name = '';

			$collection = $this->postFactory->create()->getCollection();
			$category_parent = $collection->addFieldToFilter('school_name_text',$school_name_post);
			$category_parent->addFieldToSelect('school_type');
			$category_parent->addFieldToSelect('school_name');

			$schoolType=$category_parent->getData();
			
			if(isset($schoolType[0]['school_type']) && isset($schoolType[0]['school_name']) ){
				$school_type=$schoolType[0]['school_type'];
				$school_name=$schoolType[0]['school_name'];
			}
			if($school_type==3 ||  $school_type==2 ){
				if($school_type == 3){
				   $userCollection = $this->postlistFactory->create()->getCollection();
				   $userData=$userCollection->addFieldToFilter('school_name_text',$school_name_post)->addFieldToFilter('admission_id',$roll_numbers);
					if(!(empty($userData->getData()))){
						$_SESSION["s_school_name"] = $school_name_post;
						$_SESSION["s_username"] = $username;
						$_SESSION["s_password"] = $password;
						$_SESSION["s_rollnumbers"] = $roll_numbers;
						$url ="/schools/schoolzone_search/index/display?schoolname=$school_name_post&rollnumbers=$roll_numbers";
						echo $url;
					} else{
						return $result->setData('roll_not_valied');
					}
				}elseif($school_type == 2){
				    $userCollection = $this->postlistFactory->create()->getCollection();
				    $userData=$userCollection->addFieldToFilter('school_name_text',$school_name_post)->addFieldToFilter('username',$username)->addFieldToFilter('password',$password);
					if(!(empty($userData->getData()))){
						$_SESSION["s_school_name"] = '';
						$_SESSION["s_school_name"] = $school_name_post;
						$_SESSION["s_username"] = $username;
						$_SESSION["s_password"] = $password;
						$_SESSION["s_rollnumbers"] = $roll_numbers;
						$url ="/schools/schoolzone_search/index/display?schoolname=$school_name_post";
						echo $url;
					} else{
						return $result->setData('username_not_valied');
					}
				}
			}
			if($school_type==1)
			{
				$_SESSION["s_school_name"] = $school_name_post;
				$_SESSION["s_username"] = $username;
				$_SESSION["s_password"] = $password;
				$_SESSION["s_rollnumbers"] = $roll_numbers;
				$url ="/schools/schoolzone_search/index/display?schoolname=$school_name_post";
				echo $url;
			}
		}

		if($type=='back'){
		 	$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
		 	echo $url =$baseUrl;
		}
		if($type=='back_orders'){
		 	$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
		 	echo $url =$baseUrl;
		}
		if($type == 'search_order_export') {
		$fileDirectoryPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
		    if(!is_dir($fileDirectoryPath))
		    mkdir($fileDirectoryPath, 0777, true);
		    $fileName = 'exportPvn.csv';
		    $filePath = '/var/www/magento24-cbs/pub/media/ubertheme/csvSamples/' . $fileName;

		    $data = [];

		      /* pass data array to write in csv file */
		    $data[] = ['ID','Purchase Point','Purchase Date','Bill-to-Name','Ship-to-name','Grand-total(Base) Rs','Grand-total(Purchased) Rs','Student Name','Roll Number','Status','E-mail','mobile'];

		    $orderId = $this->getRequest()->getPost('orderId');
			$status = $this->getRequest()->getPost('status');
			$rollNumber = $this->getRequest()->getPost('rollNumber');
			$searchClass = $this->getRequest()->getPost('searchClass');
			$searchSchool = $this->getRequest()->getPost('searchSchool');
			$startDate = date("Y-m-d h:i:s",strtotime('2026-01-01 00:00:00')); // start date	
			$endDate = date("Y-m-d h:i:s", strtotime('2032-01-01 23:59:59')); // end date
			if(($status != '') && ($orderId != '')){
				if(($rollNumber != '')){
						$collection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
					 ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('increment_id',$orderId)
					 ->addFieldToFilter('status', $status)
					 ->addFieldToFilter('roll_no', $rollNumber);
				}else{
					$collection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('increment_id',$orderId)
					 ->addFieldToFilter('status', $status);
				}
			}elseif(($status != '') &&($orderId == '')){
				if(($rollNumber != '')){
						$collection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
				 ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('status', $status)
					 ->addFieldToFilter('roll_no', $rollNumber);
				}else{
					$collection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('status', $status);
				}
			}elseif(($status != '') &&($orderId == '')){
				$collection = $this->_orderCollectionFactory->create()
				 ->addAttributeToSelect('*')
				 ->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
				->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
				 ->addFieldToFilter('status', $status);
			}elseif(($orderId != '') && ($status == '')){
				if(($rollNumber != '')){
					$collection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
				->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('increment_id',$orderId)
					 ->addFieldToFilter('roll_no', $rollNumber);
				}else{
						$collection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
				->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('increment_id',$orderId);
				}
			} elseif (($orderId == '') && ($status == '')){
				if(($rollNumber != '')){
					$collection = $this->_orderCollectionFactory->create()
					->addAttributeToSelect('*')
					->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
				   ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					->addFieldToFilter('roll_no', $rollNumber);
				} else {
				$collection = $this->_orderCollectionFactory->create()
				 	->addAttributeToSelect('*')
				 	->addFieldToFilter('school_id', ['in'=>$_SESSION['school_name']])
					 ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate));
				}
			}

				
				 $html='<tr><th>ID<th>Purchase Point<th>Purchase Date ger<th>Bill-to-Name<th>Ship-to-name<th>Grand-total(Base) Rs<th>Grand-total(Purchased) Rs<th>Student Name<th>Roll Number<th>School Name<th>Status<th>E-mail<th>mobile<th>Product';
				 $telephone='';
				foreach ($collection as $value) {
						$order=$this->order->load($value['entity_id']);
		
				   if($value['customer_id']!=''){
						$customer = $this->_customerRepositoryInterface->getById($value['customer_id']);
					    $billingAddressId = $customer->getDefaultBilling();
                        $addresses = $customer->getAddresses();
						$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						$customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($value['customer_id']);
						$customerAddress = array();
						foreach ($customerObj->getAddresses() as $address) {
							$customerAddress[] = $address->toArray();
						}
						foreach ($customerAddress as $customerAddres) {
						  $telephone = $customerAddres['telephone'];
						
					}

					 		//$billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
					    	//$telephone = $billingAddress->getTelephone();
						}

							$products = array();
							foreach ($order->getItems() as $orderValue) {
								if($orderValue['product_type'] == 'bundle'){
									$productRepo = $this->_productRepository->getById($orderValue['product_id']);

								$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$product = $objectManager->create('Magento\Catalog\Model\Product')->load($orderValue['product_id']);
							   $shippingCode = $product->getData('class_school');

									if(($searchClass != '')){
											if($shippingCode == $searchClass){

											}
									}else{

									}
								}
									$products[] =  $orderValue['name'];
									
								
							}

							//echo "<pre>";print_r($products);die;
				}

             $fileName = 'csv_filename.csv';
     $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
         . "/" . $fileName;
 
     $customer = $this->_customerSession->getCustomer();
     $personalData = $this->getPresonalData($customer);
 
     $this->csvProcessor
    	    ->setDelimiter(';')
         ->setEnclosure('"')
         ->saveData(
             $filePath,
             $personalData
         );
 
     return $this->fileFactory->create(
         $fileName,
         [
             'type' => "filename",
             'value' => $fileName,
             'rm' => true,
         ],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
         'application/octet-stream'
     );

			    
		$this->csvProcessor
			->setEnclosure('"')
			->setDelimiter(',')
			->saveData($filePath, $data);
		   
		$baseUrl = $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
		$csvUrl =  $baseUrl.'ubertheme/csvSamples/'.$fileName;
		//echo $csvUrl; return;
		}

		if ($type === 'search_by_order') {

		$customerEmail = trim((string) $this->getRequest()->getPost('customerEmail'));
		$phoneNumber   = trim((string) $this->getRequest()->getPost('phoneNumber'));
		$orderId       = trim((string) $this->getRequest()->getPost('orderId'));
		$status        = trim((string) $this->getRequest()->getPost('status'));
		$rollNumber    = trim((string) $this->getRequest()->getPost('rollNumber'));
		$sdatepicker   = trim((string) $this->getRequest()->getPost('sdatepicker'));
		$edatepicker   = trim((string) $this->getRequest()->getPost('edatepicker'));
		$searchSchool  = trim((string) $this->getRequest()->getPost('searchSchool'));
        $schoolFilter = $searchSchool ?: ($_SESSION['school_name'] ?? []);
        /** Pagination */
		$pageSize = (int) ($this->getRequest()->getParam('limit') ?: 50);
		$page     = (int) ($this->getRequest()->getParam('p') ?: 1);
		$startDate = '2026-01-01 00:00:00';
		$endDate   = '2040-12-01 23:59:59';

    /** Base collection */
    $orderCollection = $this->_orderCollectionFactory->create();
	$connection = $orderCollection->getConnection();
    $orderCollection->addFieldToSelect([
        'entity_id',
        'increment_id',
        'created_at',
        'status',
        'grand_total',
        'subtotal',
        'customer_email',
        'school_name',
        'roll_no',
        'student_name',
        'store_id'
    ]);

    /** Base filters */
    $orderCollection->addFieldToFilter('main_table.school_id', ['in' => $schoolFilter]);
    $orderCollection->addFieldToFilter('main_table.created_at', [
        'from' => $startDate,
        'to'   => $endDate
    ]);
    $orderCollection->addFieldToFilter('main_table.status', ['nin' => ['order_split']]);

     /** Dynamic filters */
    if ($orderId) {
        $orderCollection->addFieldToFilter('main_table.increment_id', $orderId);
    }

    if ($status) {
        $orderCollection->addFieldToFilter('main_table.status', $status);
    }

    if ($rollNumber) {
        $orderCollection->addFieldToFilter('main_table.roll_no', $rollNumber);
    }

    if ($customerEmail) {
        $orderCollection->addFieldToFilter('main_table.customer_email', $customerEmail);
    }

    if ($sdatepicker && $edatepicker) {
        $orderCollection->addFieldToFilter('main_table.created_at', [
            'from' => $sdatepicker . ' 00:00:00',
            'to'   => $edatepicker . ' 23:59:59'
        ]);
    }

   /** Join billing address */
    $orderCollection->getSelect()->joinLeft(
        ['billing' => $orderCollection->getTable('sales_order_address')],
        "main_table.entity_id = billing.parent_id AND billing.address_type = 'billing'",
        [
            'billing_firstname' => 'billing.firstname',
            'billing_lastname'  => 'billing.lastname'
        ]
    );

    /** Join shipping address */
    $orderCollection->getSelect()->joinLeft(
        ['shipping' => $orderCollection->getTable('sales_order_address')],
        "main_table.entity_id = shipping.parent_id AND shipping.address_type = 'shipping'",
        [
            'shipping_firstname' => 'shipping.firstname',
            'shipping_lastname'  => 'shipping.lastname',
            'shipping_telephone' => 'shipping.telephone'
        ]
    );

    /** Phone filter */
    if ($phoneNumber) {
        $orderCollection->getSelect()->where(
            'shipping.telephone LIKE ?',
            '%' . $phoneNumber . '%'
        );
    }

    /** Pagination */
    $orderCollection->setPageSize($pageSize)
                    ->setCurPage($page);

    $filterCount = $orderCollection->getSize();

    /** Preload store names once */
    $stores = [];
    foreach ($this->_storeManager->getStores() as $store) {
        $stores[$store->getId()] = $store->getName();
    }
    /** HTML generation */
    $html = '';
	  $html = '<tr>
        <th>ID</th>
        <th>Purchase Point</th>
        <th>Purchase Date</th>
        <th>Bill-to Name</th>
        <th>Ship-to Name</th>
        <th>Grand Total</th>
        <th>Student Name</th>
        <th>Roll Number</th>
        <th>School Name</th>
        <th>Status</th>
        <th>Email</th>
        <th>Phone</th>
    </tr>';
    foreach ($orderCollection as $order) {
        $storeName = $stores[$order->getStoreId()] ?? '';
        $html .= '<tr>';
        $html .= '<td>' . $order->getIncrementId() . '</td>';
        $html .= '<td>' . $storeName . '</td>';
        $html .= '<td>' . $order->getCreatedAt() . '</td>';
        $html .= '<td>' . $order->getData('billing_firstname') . ' ' . $order->getData('billing_lastname') . '</td>';
        $html .= '<td>' . $order->getData('shipping_firstname') . ' ' . $order->getData('shipping_lastname') . '</td>';
        $html .= '<td>' . $order->getGrandTotal() . '</td>';
        $html .= '<td>' . $order->getStudentName() . '</td>';
        $html .= '<td>' . $order->getRollNo() . '</td>';
        $html .= '<td>' . $order->getSchoolName() . '</td>';
        $html .= '<td>' . $order->getStatus() . '</td>';
        $html .= '<td>' . $order->getCustomerEmail() . '</td>';
        $html .= '<td>' . $order->getData('shipping_telephone') . '</td>';
        $html .= '</tr>';
    }
    /** Pager block */
    $pagerBlock = $this->_view->getLayout()
        ->createBlock(\Magento\Theme\Block\Html\Pager::class)
        ->setAvailableLimit([50 => 50, 100 => 100, 200 => 200])
        ->setCollection($orderCollection)
        ->toHtml();

    return $result->setData([
        'html'        => $html,
        'pager'       => $pagerBlock,
        'filterCount' => $filterCount
    ]);
}


        
        if($type == 'search_by_orderdup') {
			//echo '<pre>';print_r($this->getRequest()->getPost());
			$orderId = $this->getRequest()->getPost('orderId');
			$status = $this->getRequest()->getPost('status');
			$rollNumber = $this->getRequest()->getPost('rollNumber');
			$customerEmail = trim($this->getRequest()->getPost('customerEmail') ?? '');
			$phoneNumber = trim($this->getRequest()->getPost('phoneNumber') ?? '');
			$sdatepicker = trim($this->getRequest()->getPost('sdatepicker') ?? '');
			$edatepicker = trim($this->getRequest()->getPost('edatepicker') ?? '');
			//echo $sdatepicker." ".$edatepicker; die;
			$searchClass = $this->getRequest()->getPost('searchClass');
			$searchSchool = $this->getRequest()->getPost('searchSchool');
		    $startDate = date("Y-m-d h:i:s",strtotime('2026-01-01 00:00:00'));
	        $endDate = date("Y-m-d h:i:s", strtotime('2040-12-01 23:59:59')); 
	        $school_name_session = $_SESSION['school_name'];
		    $filterCount = 0;
		    $filterCollection = $this->_orderCollectionFactory->create()->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate)); 
            if(empty($searchSchool)) {
				$school_name_filter = $school_name_session;
			} else {
			    $school_name_filter = $searchSchool;
			}
			if(($status != '') && ($orderId != '')){
				if(($rollNumber != '')){
						$ordercollection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					 ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('increment_id',$orderId)
					 ->addFieldToFilter('status', $status)
					 ->addFieldToFilter('roll_no', $rollNumber);
					$filterCount = $ordercollection->getSize();
				} else {
					$ordercollection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('increment_id',$orderId)
					 ->addFieldToFilter('status', $status);
					$filterCount = $ordercollection->getSize();
				}
			} elseif(($status != '') &&($orderId == '')) {
				if(($rollNumber != '')){
					$ordercollection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('status', $status)
					 ->addFieldToFilter('roll_no', $rollNumber);
					$filterCount = $ordercollection->getSize();
				}else{
					$ordercollection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					->addFieldToFilter('status', $status);
					$filterCount = $ordercollection->getSize();
				}
			} elseif(($status != '') &&($orderId == '')) {
				 $ordercollection = $this->_orderCollectionFactory->create()
				 ->addAttributeToSelect('*')
				 ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
			->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
			->addFieldToFilter('status', $status);
			$filterCount = $ordercollection->getSize();
			} elseif(($orderId != '') && ($status == '')) {
				if(($rollNumber != '')){
					$ordercollection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					 ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
				 	 ->addFieldToFilter('status', array('nin' => array('order_split')))
					 ->addFieldToFilter('increment_id',$orderId)
					 ->addFieldToFilter('roll_no', $rollNumber);
					$filterCount = $ordercollection->getSize();
				}else{
				         $ordercollection = $this->_orderCollectionFactory->create()
					 ->addAttributeToSelect('*')
					 ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
				         ->addFieldToFilter('status', array('nin' => array('order_split')))
  					 ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					 ->addFieldToFilter('increment_id',$orderId);
				 	$filterCount = $ordercollection->getSize();
				}
			} elseif(($orderId == '') && ($status == '')) {
				if(($rollNumber != '')){
					$ordercollection = $this->_orderCollectionFactory->create()
					->addAttributeToSelect('*')
					->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					->addFieldToFilter('status', array('nin' => array('order_split')))
					->addFieldToFilter('roll_no', $rollNumber);
					$filterCount = $ordercollection->getSize();
				} else {
					
				$ordercollection = $this->_orderCollectionFactory->create()
				 	->addAttributeToSelect('*')
				 	->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					 //->addFieldToFilter('school_id', $school_name_filter)
				->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate));
			//echo '<pre>';print_r($ordercollection->getData());die;
				  $filterCount = $ordercollection->getSize();
				}
			
                                if(($customerEmail != '')){
                                        $ordercollection = $this->_orderCollectionFactory->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					->addFieldToFilter('status', array('nin' => array('order_split')))
					->addFieldToFilter('customer_email', $customerEmail);
					$filterCount = $ordercollection->getSize();
                                }

				if(($phoneNumber != '')){
                                        $ordercollection = $this->_orderCollectionFactory->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
				        ->addFieldToFilter('status', array('nin' => array('order_split')))
					->join(
					        ['address' => 'sales_order_address'],
					         'main_table.entity_id = address.parent_id AND address.address_type = "shipping"',
				                ['telephone']
                                         )
					 ->addFieldToFilter('address.telephone', ['like' => '%' . $phoneNumber . '%']);
					$filterCount = $ordercollection->getSize();
                                        
				}

				if(($phoneNumber != '' && $customerEmail != '' && $sdatepicker != '' && $edatepicker != '')){
                                        $ordercollection = $filterCollection //this->_orderCollectionFactory->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
                                        ->addAttributeToFilter('created_at', array('from'=>$sdatepicker." 00:00:00", 'to'=>$edatepicker." 23:59:59"))
                                        ->addFieldToFilter('status', array('nin' => array('order_split')))
                                        ->addFieldToFilter('customer_email', $customerEmail)
                                        ->join(
                                                ['address' => 'sales_order_address'],
                                                 'main_table.entity_id = address.parent_id AND address.address_type = "shipping"',
                                                ['telephone']
                                         )
					 ->addFieldToFilter('address.telephone', ['like' => '%' . $phoneNumber . '%']);
					$filterCount = $ordercollection->getSize();

                                }


				if(($phoneNumber != '' && $customerEmail != '')){
                                        $ordercollection = $this->_orderCollectionFactory->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
                                        ->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate))
					->addFieldToFilter('status', array('nin' => array('order_split')))
				        ->addFieldToFilter('customer_email', $customerEmail)
                                        ->join(
                                                ['address' => 'sales_order_address'],
                                                 'main_table.entity_id = address.parent_id AND address.address_type = "shipping"',
                                                ['telephone']
                                         )
					 ->addFieldToFilter('address.telephone', ['like' => '%' . $phoneNumber . '%']);
					$filterCount = $ordercollection->getSize();

				}

				if($phoneNumber != '' && $sdatepicker != '' && $edatepicker != ''){
                                        $ordercollection = $$filterCollection //this->_orderCollectionFactory->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
                                        ->addAttributeToFilter('created_at', array('from'=>$sdatepicker." 00:00:00", 'to'=>$edatepicker." 23:59:59"))
                                        ->addFieldToFilter('status', array('nin' => array('order_split')))
                                        ->join(
                                                ['address' => 'sales_order_address'],
                                                 'main_table.entity_id = address.parent_id AND address.address_type = "shipping"',
                                                ['telephone']
                                         )
					 ->addFieldToFilter('address.telephone', ['like' => '%' . $phoneNumber . '%']);
					$filterCount = $ordercollection->getSize();
				}

				if(($sdatepicker != '') && ($edatepicker != '')){
                                        $ordercollection = $filterCollection //this->_orderCollectionFactory->create()
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('school_id', ['in'=>$school_name_filter])
					->addAttributeToFilter('created_at', array('from'=>$sdatepicker." 00:00:00", 'to'=>$edatepicker." 23:59:59"))
					->addFieldToFilter('status', array('nin' => array('order_split')));
					 if ($customerEmail != '') {
						 $ordercollection->addFieldToFilter('customer_email', ['eq' => $customerEmail]);
					 }
					 
					if($phoneNumber != ''){			        
	  				  $ordercollection->join(
                                                ['address' => 'sales_order_address'],
                                                 'main_table.entity_id = address.parent_id AND address.address_type = "shipping"',
                                                ['telephone']
                                              )
					 ->addFieldToFilter('address.telephone', ['like' => '%' . $phoneNumber . '%']);

					}

					$filterCount = $ordercollection->getSize();
				} 	
			}
		
			$pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 10;
			$page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
			$ordercollection -> addFieldToFilter('status', array('nin' => array('order_split')))
				-> setPageSize($pageSize)->setCurPage($page); 
			
			//->addFieldToFilter('status', array('nin' => array('order_split')))
			//	        ->setPageSize($pageSize)->setCurPage($page);
			//echo $ordercollection->getSelect()->__toString();die;
	        $html='<tr><th>ID<th>Purchase Point<th>Purchase Date ger<th>Courier Name<th>Tracking Number<th>Dispatched Date<th>Bill-to-Name<th>Ship-to-name<th>Grand-total(Base) Rs<th>Grand-total(Purchased) Rs<th>Student Name<th>Roll Number<th>School Name<th>Status<th>E-mail<th>mobile<th>Product';
			$telephone='';
			$shippingPhoneNumber = '';
			$shippingAddressName = '';
			$billingAddressName = '';
			$storeName = '';

			foreach ($ordercollection as $value)
			{
               		        $order = $this->order->load($value['entity_id']);

				if($value['customer_id']!=''){
					$customer = $this->_customerRepositoryInterface->getById($value['customer_id']);
					$billingAddressId = $customer->getDefaultBilling();
					$addresses = $customer->getAddresses();
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($value['customer_id']);
					$customerAddress = array();
					foreach ($customerObj->getAddresses() as $address) {
						$customerAddress[] = $address->toArray();
					}
					foreach ($customerAddress as $customerAddres) {
					  $telephone = $customerAddres['telephone'];
						
					}
					//$billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
							//$telephone = $billingAddress->getTelephone();
				}
				if($value['entity_id']!=''){

				  $order = $this->_orderCollectionFactory->create()->addFieldToFilter('entity_id', $value['entity_id'])->getFirstItem();
				  
           			  if ($order && $order->getShippingAddress()) {
					  $shippingAddress = $order->getShippingAddress();
					  $billingAddress = $order->getBillingAddress(); 
					  $shippingPhoneNumber =  $shippingAddress->getTelephone();
				 	  $shippingAddressName = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname(); 
					  $billingAddressName = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                                  }
				  if ($order && $order->getStoreId()) {
               				 $storeId = $order->getStoreId();
                			 $store = $this->_storeManager->getStore($storeId);
               				 $storeName = $store->getName();
                		   }


				}
				$products = array();
				foreach ($order->getItems() as $orderValue) {
					if($orderValue['product_type'] == 'bundle') {

					//echo "<pre>"; print_r($orderValue['name']);die;
			//		$productRepo = $this->_productRepository->getById($orderValue['product_id']);

					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			//		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($orderValue['product_id']);
					$shippingCode = ""; //$product->getData('class_school');
                    // $attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
					// $options = $attribute->getSource()->getAllOptions();	
					// foreach ($options as $valueOption) {
					// 	if($shippingCode == $valueOption['value']){
					// 		// $logger->info($valueOption['label']);
					// 	}	
					// }
                    $trresource = $objectManager->get('Magento\Framework\App\ResourceConnection');
					$connection = $trresource->getConnection();
					$cbotableName = $trresource->getTableName('cbo_assign_shippment');
					$cbodrivertableName = $trresource->getTableName('cboshipping_autodrivers');
					$deliveryboytableName = $trresource->getTableName('deliveryboy_deliveryboy'); 

					$orderid = $value['entity_id'];
					$cbosql = "Select * FROM ". $cbotableName." WHERE order_id = $orderid ";
					$trresult = $connection->fetchRow($cbosql);
					if(!empty($trresult)) {
						// echo '<pre>';print_r($trresult);die;
						if(isset($trresult['tracking_title'])){
							$tracking_title = $trresult['tracking_title'];
							$tracking_number = $trresult['tracking_number'];
							$trcreated_at = $trresult['created_at'];
						} else {
							$driver_id = $trresult['driver_id'];
							$deliveryboy_id = $trresult['deliveryboy_id'];
							if(!empty($driver_id)){
							$cbodriversql = "Select * FROM ". $cbodrivertableName." WHERE id = $driver_id ";
							$cbodriverresult = $connection->fetchRow($cbodriversql);
							if(!empty($cbodriverresult['driver_name'])){
							$drivername = 'Driver Name: '.$cbodriverresult['driver_name'];
							}
							$tracking_title = 'CBO Shipment ';
							$tracking_number = $drivername;
							$trcreated_at = $trresult['created_at'];
							}
							if(!empty($deliveryboy_id)){
							$deliveryboysql = "Select * FROM ". $deliveryboytableName." WHERE id = $deliveryboy_id ";
							$deliveryboyresult = $connection->fetchRow($deliveryboysql);
							$deliveryboyname ='';
							if(!empty($deliveryboyresult['name'])){
							$deliveryboyname = 'Deliveryboy Name: '.$deliveryboyresult['name'];
							}
							$tracking_title = 'CBO Shipment ';
							$tracking_number = $deliveryboyname;
							$trcreated_at = $trresult['created_at'];
							}
						}
					} else {
						$tracking_title = '';
						$tracking_number = '';
						$trcreated_at = ''; 
					}
					//$tracking_title; $tracking_number; $created_at;
					if(($searchClass != '')) {
						if($shippingCode == $searchClass){
						  $html.="<tr><td>".$value['increment_id']."<td>".$storeName."<td>".$value['created_at']."<td>".$tracking_title."<td>".$tracking_number."<td>".$trcreated_at."<td>".$billingAddressName."<td>".$shippingAddressName."<td>".$value['subtotal']."<td>".$value['grand_total']."<td>".$value['student_name']."<td>".$value['roll_no']."<td>".$value['school_name']."<td>".$value['status']."<td>".$value['customer_email']."<td>".$shippingPhoneNumber."<td>".$orderValue['name'];
						}
					} else {
					 $html.="<tr><td>".$value['increment_id']."<td>".$storeName."<td>".$value['created_at']."<td>".$tracking_title."<td>".$tracking_number."<td>".$trcreated_at."<td>".$billingAddressName."<td>".$shippingAddressName."<td>".$value['subtotal']."<td>".$value['grand_total']."<td>".$value['student_name']."<td>".$value['roll_no']."<td>".$value['school_name']."<td>".$value['status']."<td>".$value['customer_email']."<td>".$shippingPhoneNumber."<td>".$orderValue['name'];
					}

					}
					$products[] =  $orderValue['name'];
				}
				}
				return $result->setData(["html" => $html, "filterCount" => $filterCount]);
		}
	}

	public function sendEmail($templateVars, $to , $from)
	{
		$transport = $this->transportBuilder
			->setTemplateIdentifier('notify_test_email_template')
			->setTemplateOptions([
				'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
				'store' => $this->_storeManager->getStore()->getId()
			])
			->setTemplateVars($templateVars)
			->setFromByScope([
				'email' => 'no-reply@centralbooksonline.com',
				'name'  => 'Central Books Online'
			])
			->addTo($from)
            ->setReplyTo($templateVars['notify_email'])
			->getTransport();

		$transport->sendMessage();
		return 'yes';
	}


	public function saveNotifyMe($data)
	{
		$model = $this->notifyReport->create();
		$model->addData([
			"name" => $data['notify_name'],
			"phone" => $data['notify_phone'],
			"email" => $data['notify_email'],
			"school_name" => $data['notify_schoolname'],
			"school_address" => $data['notify_schooladdress'],
			"message" => $data['notify_message'],
			"notify_status" => 'New', // New
			"is_deleted" => "false" // not deleted(false)
			]); 
		$saveData = $model->save();
		$lastId = $model->getId();
		$response = array();
		if($saveData){
			$response['message'] = 'saved';
			$response['id'] = $lastId;
			// return 'saved';
		}else{
			$response['message'] = 'not_saved';
			// return 'not_saved';
		}
		return $response;
	}
}

