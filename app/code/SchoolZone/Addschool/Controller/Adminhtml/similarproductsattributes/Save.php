<?php
namespace SchoolZone\Addschool\Controller\Adminhtml\similarproductsattributes;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{
    protected $eavConfig;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        \SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolsCollection,
        \Magento\Eav\Model\Config $eavConfig,
        Action\Context $context)
    {
        $this->schoolsCollection = $schoolsCollection;
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
            $model = $this->_objectManager->create('SchoolZone\Addschool\Model\Similarproductsattributes');
            $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
            $options = $attribute->getSource()->getAllOptions();

            $name = '';
            foreach ($options as $value) {
                if($value['value'] == $data['school_name']){
                    $name = $value['label']; 
                }
            }

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
                
            }else{
                $collection = $this->schoolsCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('school_name_text', $name);
                    if($collection->getFirstItem()->getData('school_name_text') != ''){
                        $this->messageManager->addError(__('Cannot create with same school name'));
                        return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                    }

               
                // for school code restrict
                $collection = $this->schoolsCollection->create()
                    ->addFieldToSelect('*');
                foreach($collection as $schoolData){
                    if($schoolData['school_code'] == $data['school_code']){
                        $this->messageManager->addError(__('Cannot create with same school code'));
                        return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                    }
                }

                
            }
            if(strlen($data['school_code']) == 0){
                $this->messageManager->addError(__('School code cannot be empty'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }

            if(strlen($data['school_code']) > 20){
                $this->messageManager->addError(__('School code value exceeded MAX (20) characters'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }

	    if ($data['enable_storepickup'] && !$data['storepickup_timings']) {
                $this->messageManager->addError(__('Please enter valid timings (e.g., 10–13).'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
	    } 

	     if ($data['enable_storepickup'] && $data['pickup_stores'] == '' ) {
                $this->messageManager->addError(__('Please select a pickup store.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }


            if ($data['storepickup_timings']) {
                $pattern = '/(2[0-3]|1[0-9]|[1-9])-(2[0-3]|1[0-9]|[1-9])/i';
                $replacedString = preg_replace($pattern, '#', $data['storepickup_timings']);
                $timings = explode('-', $data['storepickup_timings']);
                if ($replacedString != '#' || ($timings[0] >= $timings[1])) {
                    $this->messageManager->addError(__('Please give valid Storepickup timings as 10-13'));
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                }                
            }
            $data['school_name_text']=$name;

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Addschool has been saved.'));
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
                $this->messageManager->addException($e, __('Something went wrong while saving the Addschool.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
