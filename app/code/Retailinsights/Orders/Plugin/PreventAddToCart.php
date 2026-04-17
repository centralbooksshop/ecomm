<?php

namespace Retailinsights\Orders\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;

class PreventAddToCart
{

    protected $variable;
	
	public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
		\Magento\Variable\Model\Variable $variable,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolCollection,
        \SchoolZone\Registration\Model\ResourceModel\Similarproductsattributes\CollectionFactory $studentFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_request = $request;
        $this->registry = $registry;
		$this->variable = $variable;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->schoolCollection = $schoolCollection;
        $this->studentFactory = $studentFactory;
    }

    public function beforeAddProduct(
        Cart $cart, 
        $productInfo, 
        $requestInfo = null)
    {   
        if($this->getWebsiteCode() == 'schools'){
            $student_name = $this->_request->getPost('student_pname');
            $student_number = $this->_request->getPost('student_pid');
            $productId = $this->_request->getPost('product');
            
            $response = $this->checkForProductInCart($productId, $productInfo);

            if($response == 'same_product'){
                throw new \Magento\Framework\Exception\LocalizedException(__("Cannot add same product to cart"));
            } else if($response == 'different_school'){
                throw new \Magento\Framework\Exception\LocalizedException(__("Unable to add products from different schools to the cart."));
			}

            // for type2 and type3 name and admission id is required
            $isNameAdmissionRequired = $this->checkForSchoolType($productId);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $school_helper = $objectManager->get('\SchoolZone\Search\Helper\Data');

            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            $schoolId = $product->getData('school_name');

            $schools = $this->schoolCollection->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('school_name',['eq'=>$schoolId]);
            $schoolType = $schools->getFirstItem()->getData('enable_roll'); 

            if($schoolType == 0){
                if($isNameAdmissionRequired == 'required'){
                    if ($student_name == '') {
                        throw new \Magento\Framework\Exception\LocalizedException(__("Please provide Student Name"));
                    }
                    $isValidAdmissionNo = $this->CheckForValidAdmissionNo($productId);
                }

            }else{
                if($isNameAdmissionRequired == 'required'){
                    if (($student_name == '') || ($student_number == '')) {
                        throw new \Magento\Framework\Exception\LocalizedException(__("Please provide Student Name and Adminssion Number"));
                    }
                    $isValidAdmissionNo = $this->CheckForValidAdmissionNo($productId);
                }
            }
        }
        return [$productInfo,$requestInfo];
    }

    public function checkForProductInCart($productId, $productInfo)
    {
        if ($productInfo instanceof Product) {
            $productId = $productInfo->getId();
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $productId = $productInfo;
        }
               
        $isSameProduct = false;
		$different_school = false;
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $quote_product_id = $item->getProductId();
            if($productId == $item->getProductId()) {
                $isSameProduct = 1;
                break;
            }
        }
		if(!empty($quote_product_id)) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
			$quote_products = $productFactory->create()                              
				->addAttributeToSelect('*')
				->addAttributeToFilter('entity_id',['eq'=>$quote_product_id])
				->setStore($this->_storeManager->getStore());
			$quote_data = $quote_products->getFirstItem();
			$quote_product = $quote_data->getData();
			$cart_school_id = $quote_product['school_name'];
			//echo '<pre>';print_r($quote_product);
			$current_school_id = $productInfo['school_name'];
			if($cart_school_id != $current_school_id)
			{
				$different_school = true;   
			}
		}
		if($isSameProduct) {
            return 'same_product';
        } else if($different_school) {
            return 'different_school';
        }
        
        return  false;
    }

    public function checkForSchoolType($productId)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $schoolId = $product->getData('school_name');

        $schools = $this->schoolCollection->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('school_name',['eq'=>$schoolId]);
        $schoolType = $schools->getFirstItem()->getData('school_type');

        if($schoolType == '1'){
            return 'required';
        }
        // 2. Get school type.
        $students = $this->studentFactory->create()                              
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('school_name',['eq'=>$schoolId]);
        if($students){
            $flag = false;
            $student_number = $this->_request->getPost('student_pid');
            $schoolName = $students->getFirstItem()->getData('school_name_text');
            
            $admissionId = $students->getFirstItem()->getData('admission_id');
            if(($admissionId != '') && ($schoolType != '1')){ // school type = 1 (no validation required)
                return 'required';
            }
        }
        return 'not_required';
    }

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }

    public function CheckForValidAdmissionNo($productId)
    {
        $admission_number_date_variable = $this->variable->loadByCode('admission_number_date', 'admin');
		$admission_number_date_value = $admission_number_date_variable->getPlainValue();
		 // Admission no. validation
        //1. take school name from product.
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $schoolId = $product->getData('school_name');

        $schools = $this->schoolCollection->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('school_name',['eq'=>$schoolId]);
        $schoolType = $schools->getFirstItem()->getData('school_type');
        if($schoolType != 1 && $schoolType != 2){

            // 2. Get school type.
            $students = $this->studentFactory->create()                              
                            ->addFieldToSelect('*')
                            ->addFieldToFilter('school_name',['eq'=>$schoolId]);
                            
            // 3. Check for adminssion no required for particular type.
            if($students){
                $flag = false;
                // $student_number = $this->_request->getPost('student_pid');
                 $student_number = $_SESSION["s_rollnumbers"];
                $schoolName = $students->getFirstItem()->getData('school_name_text');
                foreach($students as $student){
                    if($student->getData('admission_id') == $student_number){
                        $flag = true;
                        
                        //  Restrict particular admission no. to purchase order.
                        // 1. Check for already ordered product.

						$now = new \DateTime();
                        $collection = $this->_orderCollectionFactory->create()
                                ->addAttributeToSelect('*')
                                ->addFieldToFilter('roll_no', $student_number)
						        ->addFieldToFilter('school_id',['eq'=>$schoolId]);

					if(!empty($admission_number_date_value)) {
						$collection->addFieldToFilter('created_at', ['lteq' => $now->format('Y-m-d H:i:s')])->addFieldToFilter('created_at', ['gteq' => $now->format("$admission_number_date_value H:i:s")]);
					}
		
						
						//Add condition if you wish
                        // 2. check order status with different scenario.
                        // 3. through error if already parchased order.

						$ordercollection = $collection->getData();

						foreach ($ordercollection as $ordercollectionval) {
							//echo "<pre>";print_r($ordercollectionval);
							
						 if($ordercollectionval['increment_id']) {
                            if($ordercollectionval['status'] != 'canceled'){
                                if($ordercollectionval['status'] != 'pending'){
                                    throw new \Magento\Framework\Exception\LocalizedException(__("Can purchase only 1 product for ".$student_number." admission no."));
                                }
                            }    
                          }
							
						}
	
                    }
                }
                if($flag == false){
                    throw new \Magento\Framework\Exception\LocalizedException(__("Invalid admission number for ".$schoolName));
                }
            }else{
                // no students with school
            }
        }
    }
}