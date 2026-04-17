<?php
namespace SchoolZone\Registration\Controller\Adminhtml\similarproductsattributes;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{
    protected $eavConfig;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        Action\Context $context)
    {
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();


        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('SchoolZone\Registration\Model\Similarproductsattributes');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }

   
            $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
            $options = $attribute->getSource()->getAllOptions();

            // $name = '';
            // foreach ($options as $value) {
            //     if($value['value'] == $data['school_name']){
            //         $name = $value['label']; 
            //     }
            // }

            // $data['school_name_text']=$name;

           
 
             $model->setSchoolName($data['school_name']);
             $model->setStudentName($data['student_name']);
             $model->setClass($data['class']);
             if(isset($data['username'])){
                $model->setUsername($data['username']);
             }
             if(isset($data['password'])){
                $model->setPassword($data['password']);
             }
             $model->setAdmissionId($data['admission_id']);
             $model->setSchoolNameText('temporary name');


            // $model->setData($data);
        

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Registration has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Registration.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}