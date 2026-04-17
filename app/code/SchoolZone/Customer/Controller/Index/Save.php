<?php
namespace SchoolZone\Customer\Controller\Index;

use Magento\Framework\Encryption\EncryptorInterface;

class Save extends \Magento\Framework\App\Action\Action
{
	protected $postaddFactory;
	protected $postFactory;
	public function __construct(
		\SchoolZone\Customer\Model\PostaddFactory $postaddFactory,
		\SchoolZone\Customer\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
		\SchoolZone\Customer\Model\PostFactory $postFactory,
		\Magento\Framework\App\Action\Context $context
		)
	{
		$this->postCollectionFactory = $postCollectionFactory;
		$this->postaddFactory=$postaddFactory;
		$this->postFactory=$postFactory;
		parent::__construct($context);
	}

	public function execute()
	{
			$key = $this->getRequest()->getParam('key');
			$studentId = $this->getRequest()->getParam('studentId');
			$school_name = $this->getRequest()->getParam('school_name');
			$student_name = $this->getRequest()->getParam('student_name');
			$student_class = $this->getRequest()->getParam('student_class');
			$admission_id = $this->getRequest()->getParam('admission_id');
			$student_username = $this->getRequest()->getParam('student_username');
			$student_password = $this->getRequest()->getParam('stud_password');
			//echo '<pre>';print_r($this->getRequest()->getParams());die;

		if($key == 'student_delete'){
            $deletStatus = '';

			$studentId = $this->getRequest()->getParam('array');
			$mandeetotcol = $this->postCollectionFactory->create();
			if(isset($studentId)){
				$count = sizeof($studentId);
					foreach ($mandeetotcol as $studentInfo) {
						foreach ($studentId as $value) {
							if($value == $studentInfo->getId()){
								try {
						            if($studentInfo->delete()){
						            	$deletStatus = 'deleted';
						            }
						        } catch (\Exception $e) {
						        	 
						        }
							}
						}
					}	
					$data = array('deletStatus'=>$deletStatus,'count'=>$count);
					echo json_encode($data);
				}else{
					$data = array('deletStatus'=>'','count'=>'');
					echo json_encode($data);
			}

		}
			
		if($key == 'student_save'){
				$school_text='';
				$post = $this->postaddFactory->create();
				$collection = $post->getCollection();
				foreach ($collection as $value) {
					if($value['school_name'] == $school_name){
						$school_text = $value['school_name_text'];
					}
				}
				$mandeetotcol = $this->postFactory->create();
				if($studentId!=''){
					$flag = 'false';
					$filterCollectionNew = $this->postCollectionFactory->create()
						->addFieldToSelect('*')
						->addFieldToFilter('school_name',$school_name)
						->addFieldToFilter('username',$student_username)
						->addFieldToFilter('admission_id',$admission_id);

						foreach($filterCollectionNew as $items){
							$id = $items->getId();
							if($id == $studentId){
								$flag = 'true';
								break;
							}
						}
						if($flag == 'false'){
							echo 'user-exists';
						}else{
							$mandeetotcol->setId($studentId);
							$mandeetotcol->setSchoolName($school_name);
							$mandeetotcol->setSchoolNameText($school_text);
							$mandeetotcol->setClass($student_class);
							$mandeetotcol->setStudentName($student_name);
							$mandeetotcol->setUsername($student_username);
							$mandeetotcol->setPassword($student_password);
							$mandeetotcol->setAdmissionId($admission_id);
							if($mandeetotcol->save()){
								echo 'saved';
							}else{
								echo "failed";
							}
						}
					
				}else{
					$filterCollection = $this->postCollectionFactory->create()
						->addFieldToSelect('*')
						->addFieldToFilter('school_name',$school_name)
						->addFieldToFilter('admission_id',$admission_id);
					if($filterCollection->getFirstItem()->getId()){
						echo 'admission-exists';
					}else{
						$filterCollection2 = $this->postCollectionFactory->create()
							->addFieldToSelect('*')
							->addFieldToFilter('school_name',$school_name)
							->addFieldToFilter('username',$student_username)
						    ->addFieldToFilter('student_name',$student_name);
						//echo '<pre>'; print_r($filterCollection2->getData()); die;

						if($filterCollection2->getFirstItem()->getId()){
							echo 'user-exists';
						}else{
							$mandeetotcol->setSchoolName($school_name);
							$mandeetotcol->setSchoolNameText($school_text);
							$mandeetotcol->setClass($student_class);
							$mandeetotcol->setStudentName($student_name);
							$mandeetotcol->setUsername($student_username);
							$mandeetotcol->setPassword($student_password);
							$mandeetotcol->setAdmissionId($admission_id);
							if($mandeetotcol->save()){
								echo 'saved';
							}else{
								echo "failed";
							}
						}
					}
				}
		}
		if($key == 'school_type'){
				$school_type='';
				$post = $this->postaddFactory->create();
				$collection = $post->getCollection();
				foreach ($collection as $value) {
					if($value['school_name'] == $school_name){
						$school_type = $value['school_type'];
					}
				}
				echo $school_type;
		}
	}
}