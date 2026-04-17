<?php
namespace SchoolZone\Customer\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class UploadCsv extends \Magento\Framework\App\Action\Action
{
        protected $resultPageFactory;
        protected $jsonHelper;
        protected $postFactory;
        protected $postaddFactory;
        protected $resultFactory;
         protected $messageManager;
         protected $eavConfig;

    public function __construct(
            \Magento\Eav\Model\Config $eavConfig,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            ResultFactory $resultFactory,
            \SchoolZone\Customer\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
            \SchoolZone\Customer\Model\PostaddFactory $postaddFactory,
            \SchoolZone\Customer\Model\PostFactory $postFactory,
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Framework\Json\Helper\Data $jsonHelper
        ) {
            $this->postCollectionFactory = $postCollectionFactory;
            $this->eavConfig = $eavConfig;
            $this->messageManager = $messageManager;
            $this->resultFactory = $resultFactory;
            $this->postaddFactory=$postaddFactory;
            $this->postFactory=$postFactory;
            $this->resultPageFactory = $resultPageFactory;
            $this->jsonHelper = $jsonHelper;
            parent::__construct($context);
        }
        public function execute()
        {
            /*$post = $this->postaddFactory->create();
            $collection = $post->getCollection();
            foreach ($collection as $key => $value) {
				$school_namearray = explode(',', $_SESSION["school_name"]);
				if(is_array($school_namearray)) {
					if (in_array($value['school_name'], $school_namearray)) {
						//if($_SESSION["school_name"] == $value['school_name']){
							// print_r($value->getData());
							$school_type = $value['school_type'];
							$name_text = $value['school_name_text'];
						//}
					}
				}
            } */

           try{
			   if(isset($_POST['submit']))
                {
					$key = 0;
                    $handle = fopen($_FILES['my_custom_file']['tmp_name'], "r");
                    $fileTypeAllowed = ['application/vnd.ms-excel','text/csv'];
                    if(!in_array($_FILES['my_custom_file']['type'], $fileTypeAllowed)){
                      $message = __('Please check file format (allowed format csv)');
                      $this->messageManager->addErrorMessage($message);
                      $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                      $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                      
                      return $resultRedirect;
                    }
                    $headers = fgetcsv($handle, 1000, ",");

                    $count=0;
                    $collection = array();
                    $collection_type_2 = array();
                  
                    while (($data_val = fgetcsv($handle, 1000, ",")) !== FALSE) {
					//echo '<pre>';print_r($data_val);die;
					if(!empty($data_val['3'])) {
                      $school_name = $data_val['3'];
					}
					$post = $this->postaddFactory->create();
					$schoolcollection = $post->getCollection();
					foreach ($schoolcollection as $schoolkey => $schoolvalue) {
					    if($school_name == $schoolvalue['school_name']){
							$school_type = $schoolvalue['school_type'];
							$name_text = $schoolvalue['school_name_text'];
						}
					}
					if(!empty($school_type)) 
					{
						if($school_type == 2){
							if($data_val['0'] == '' || $data_val['1'] == '' || $data_val['2'] == '' ||$data_val['3'] == '' || $data_val['4'] == ''){
									$key = $key+1;
									$count = $count+1;
								  $message = __('Cant be empty at line :'.$count);
								  $this->messageManager->addErrorMessage($message);
								  // echo 'Cant be empty at liness :'.$count;
								  $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
							  $resultRedirect->setUrl($this->_redirect->getRefererUrl());
							  
							  return $resultRedirect;
							} else{
								$count = $count+1;
							}
						$collection[] = $data_val;
						$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
						$resultRedirect->setUrl($this->_redirect->getRefererUrl());
						// return $resultRedirect;
						}
						if($school_type == 3){
						  if($data_val['0'] == '' || $data_val['1'] == '' || $data_val['2'] == ''){
								$key = $key+1;
								$count = $count+1;
								$message = __('Cant be empty at line :'.$count);
								$this->messageManager->addErrorMessage($message);
								// echo 'Cant be empty at line :'.$count;
								$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
								$resultRedirect->setUrl($this->_redirect->getRefererUrl());
								
								return $resultRedirect;
							}else{
								 $count = $count+1;
							}
						  $collection_type_2[] = $data_val;
						}
						$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
						$resultRedirect->setUrl($this->_redirect->getRefererUrl());
					}
					// return $resultRedirect;
                    }
                         //echo $key; die;
							if($key == 0){
                                if($school_type == 2){
                                    $attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
                                    $options = $attribute->getSource()->getAllOptions();

                                    foreach ($collection as  $value) {
                                      foreach ($options as $class) {
                                        if($class['label'] == $value[0]){
                                          $class_name = $class['label'];
                                        
                                        }
                                      }
                                      if(isset($class_name)){
                                      }else{
                                        $message = __('Please enter Valid Class Name');
                                        $this->messageManager->addErrorMessage($message);
                                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                        
                                        return $resultRedirect;
                                        
                                        //  echo "Please enter Valid Class Name";
                                        //  die;
                                      }
                                        $mandeetotcol = $this->postFactory->create();
                                        $updateId = 0;
                                         $validate = $mandeetotcol->getCollection()
                                            ->addFieldToFilter('admission_id', $value[4]);
                                            foreach ($validate as $key => $val_value) {
                                                $updateId = $val_value['id'];
                                            }
                                         
                                        if($updateId >0){
                                          $flag = 'false';
                                          $filterCollectionNew = $this->postCollectionFactory->create()
                                              ->addFieldToSelect('*')
                                              ->addFieldToFilter('school_name',['in'=>$school_name])
                                              ->addFieldToFilter('username',$value[2]);
                                          
                                              foreach($filterCollectionNew as $items){
                                                $id = $items->getId();
                                                if($id != $updateId){
                                                  $flag = 'true';
                                                  break;
                                                }
                                              }
                                              if($flag == 'true'){
                                                $message = __('Username already exists');
                                                  $this->messageManager->addErrorMessage($message);
                                                  $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                                  $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                                  
                                                  return $resultRedirect;
                                                // echo 'User already exists';
                                              }else{
                                                  $mandeetotcol->setId($updateId);
                                                  $mandeetotcol->setSchoolName($school_name);
                                                  $mandeetotcol->setSchoolNameText($name_text);
                                                  $mandeetotcol->setClass($class_name);
                                                  $mandeetotcol->setStudentName($value[1]);
                                                  $mandeetotcol->setUsername($value[2]);
                                                  $mandeetotcol->setPassword($value[3]);
                                                  $mandeetotcol->setAdmissionId($value[4]);
                                                  $mandeetotcol->save();

                                                  $message = __('Import Success');
                                                  $this->messageManager->addSuccessMessage($message);
                                              }
                                        }else{
                                          $filterCollection = $this->postCollectionFactory->create()
                                              ->addFieldToSelect('*')
                                              ->addFieldToFilter('school_name',['in'=>$school_name])
                                              ->addFieldToFilter('admission_id',$value[4]);
                                            if($filterCollection->getFirstItem()->getId()){
                                              $message = __('Admission No. already exists for '.$value[4]);
                                                  $this->messageManager->addErrorMessage($message);
                                                  $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                                  $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                                  
                                                  return $resultRedirect;
                                            }else{
                                              $filterCollection2 = $this->postCollectionFactory->create()
                                                  ->addFieldToSelect('*')
                                                  ->addFieldToFilter('school_name',['in'=>$school_name])
                                                  ->addFieldToFilter('username',$value[2]);
                                                if($filterCollection2->getFirstItem()->getId()){
                                                  $message = __('User already exists : '.$value[2]);
                                                  $this->messageManager->addErrorMessage($message);
                                                  // echo 'User already exists';
                                                  // die();
                                                  $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                                  $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                                  
                                                  return $resultRedirect;
                                                }else{
                                                    $mandeetotcol->setSchoolName($school_name);
                                                    $mandeetotcol->setSchoolNameText($name_text);
                                                    $mandeetotcol->setClass($class_name);
                                                    $mandeetotcol->setStudentName($value[1]);
                                                    $mandeetotcol->setUsername($value[2]);
                                                    $mandeetotcol->setPassword($value[3]);
                                                    $mandeetotcol->setAdmissionId($value[4]);
                                                    $mandeetotcol->save();

                                                    $message = __('Import Success');
                                                    $this->messageManager->addSuccessMessage($message);
                                                }
                                            }
                                        }
                                        
                                      }
                                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                        
                                        return $resultRedirect;
                                }
                                if($school_type == 3){
                                  $attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
                                  $options = $attribute->getSource()->getAllOptions();

                                  

                                    foreach ($collection_type_2 as  $value) {
                                      foreach ($options as $class) {
                                        if($class['label'] == $value[0]){
                                          $class_name = $class['label'];
                                        }
                                      }

                                    if(isset($class_name)){
                                      }else{
                                        $message = __('Please enter Valid Class Name');
                                        $this->messageManager->addErrorMessage($message);
                                        
                                        //  echo "Please enter Valid Class Name";
                                        //  die;
                                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                        
                                        return $resultRedirect;
                                      }

                                  
                                        $mandeetotcol = $this->postFactory->create();

                                        $updateId=0;
                                         $validate = $mandeetotcol->getCollection()
											->addFieldToFilter('school_name',['in'=>$school_name])
                                            ->addFieldToFilter('admission_id', $value[2]);
                                            foreach ($validate as $key => $val_value) {
                                                $updateId = $val_value['id'];  
                                            }
                                    if($updateId >0){
                                         $mandeetotcol->setId($updateId);
                                        $mandeetotcol->setSchoolName($school_name);
                                        $mandeetotcol->setSchoolNameText($name_text);
                                        $mandeetotcol->setClass($class_name);
                                        $mandeetotcol->setStudentName($value[1]);
                                        $mandeetotcol->setAdmissionId($value[2]);
                                        $mandeetotcol->save();

                                        $message = __('Import Success');
                                        $this->messageManager->addSuccessMessage($message);

                                    }else{
                                        $filterCollection = $this->postCollectionFactory->create()
                                        ->addFieldToSelect('*')
                                        ->addFieldToFilter('school_name',['in'=>$school_name])
                                        ->addFieldToFilter('admission_id',$value[2]);
										if($filterCollection->getFirstItem()->getId()){
											$message = __('Admission no already exists : '.$value[2]);
												$this->messageManager->addErrorMessage($message);
												$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
												$resultRedirect->setUrl($this->_redirect->getRefererUrl());
												
												return $resultRedirect;
										} else {
											$mandeetotcol->setSchoolName($school_name);
											$mandeetotcol->setSchoolNameText($name_text);
											$mandeetotcol->setClass($class_name);
											$mandeetotcol->setStudentName($value[1]);
											$mandeetotcol->setAdmissionId($value[2]);
											$mandeetotcol->save();

											$message = __('Import Success');
											$this->messageManager->addSuccessMessage($message);
                                        }
                                    }
                                        
                                    }
                                        //$message = __('Import Successful');
                                        $this->messageManager->addSuccessMessage($message);
										$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                                        return $resultRedirect;
                                }
                            } else { 
							$message = __('Import Failed');
							$this->messageManager->addErrorMessage($message);
							$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
							$resultRedirect->setUrl($this->_redirect->getRefererUrl());
                            return $resultRedirect;
							}

                }
            }catch (\Exception $e) {
                print_r($e->getMessage());
        }
    }
}



