<?php
namespace SchoolZone\Search\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    protected $postlistFactory;
    protected $postFactory;
 
    public function __construct(
       \Magento\Framework\App\Helper\Context $context, 
       \SchoolZone\Search\Model\PostFactory $postFactory,
       \SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolCollection,
        \SchoolZone\Search\Model\PostlistFactory $postlistFactory
    ) {
      $this->schoolCollection = $schoolCollection;
       $this->postlistFactory = $postlistFactory;
       $this->postFactory = $postFactory;
        parent::__construct($context);
    }
 
    /** Set Custom Cookie using Magento 2 */

    public function getSchool($schoolId){
      $schools = $this->schoolCollection->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('school_name',['eq'=>$schoolId]);
        $schoolType = $schools->getFirstItem()->getData('enable_roll');
        
      return $schoolType;
    }

	

	public function getSchoolDetails($school_name) {

	  $school_name_post = $school_name;
	  
	  $collection = $this->postFactory->create()->getCollection();
	  $schooldetails = $collection->addFieldToFilter('school_name_text',$school_name_post);
	  $school_willbegiven = $schooldetails->getFirstItem()->getData('willbegiven');
      return $school_willbegiven;
	}

	public function getSchoolGivenDetails($school_name) {

	  $school_name_post = $school_name;
	  
	  $collection = $this->postFactory->create()->getCollection();
	  $schooldetails = $collection->addFieldToFilter('school_name_text',$school_name_post);
	  $school_willbegiven = $schooldetails->getFirstItem()->getData('schoolgiven');
      return $school_willbegiven;
	}
   public function getUserDetails($school_name,$username,$password,$roll_numbers,$class) {

      $school_name_post = $school_name;
      $username = $username;
      $password = $password;
      $roll_numbers = $roll_numbers;
      $school_type = '';
      $school_name = '';
      $userInfo = array();
      $collection = $this->postFactory->create()->getCollection();
      $category_parent = $collection->addFieldToFilter('school_name_text',$school_name_post);
      $category_parent->addFieldToSelect('school_type');
      $category_parent->addFieldToSelect('school_name');

      $schoolType=$category_parent->getData();
      
      if(isset($schoolType[0]['school_type']) && isset($schoolType[0]['school_name']) ){
        $school_type=$schoolType[0]['school_type'];
        $school_name=$schoolType[0]['school_name'];
      } else {
        $userInfo['status'] = 'error';
         $userInfo['type'] = '0';
         $userInfo['student_name'] = '';
          $userInfo['admission_id'] = '';
      }
      if($school_type==3 ||  $school_type==2 ){
        if($school_type == 3){
           $userCollection = $this->postlistFactory->create()->getCollection();
           $userData=$userCollection->addFieldToFilter('school_name_text',$school_name_post)->addFieldToFilter('admission_id',$roll_numbers);
          if(!(empty($userData->getData()))){
            $userData=$userCollection->addFieldToFilter('school_name_text',$school_name_post)->addFieldToFilter('admission_id',$roll_numbers);
          $userInfo['type'] = '3';
          $userInfo['student_name'] = $userData->getData()[0]['student_name'];
          $userInfo['admission_id'] = $userData->getData()[0]['admission_id'];
          $userInfo['status'] = 'success';
          } else{
            $userInfo['type'] = '3';
            $userInfo['status'] = 'error';
            $userInfo['student_name'] = '';
          $userInfo['admission_id'] = '';
          }
        }elseif($school_type == 2){
            $userCollection = $this->postlistFactory->create()->getCollection();
            $userData=$userCollection->addFieldToFilter('school_name_text',$school_name_post)->addFieldToFilter('username',$username)->addFieldToFilter('password',$password);
          if(!(empty($userData->getData()))){
             $userInfo['type'] = '2';
          $userInfo['student_name'] = $userData->getData()[0]['student_name'];
          $userInfo['admission_id'] =  $userData->getData()[0]['admission_id'];
          $userInfo['status'] = 'success';
          } else{
            $userInfo['type'] = '2';
            $userInfo['status'] = 'error';
            $userInfo['student_name'] = '';
          $userInfo['admission_id'] = '';
          }
        }
      }
      if($school_type==1)
      {
          $userInfo['type'] = '1';
           $userInfo['student_name'] = '';
          $userInfo['admission_id'] = '';
          $userInfo['status'] = 'success';
      }

      return $userInfo;
      
   }

   public function getData($school_name,$username,$password,$roll_numbers,$class) {
    


   }

}