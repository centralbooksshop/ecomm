<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Index;

class Delete extends \Cynoinfotech\StorePickup\Controller\Adminhtml\StorePickup
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     **/
     
     /**
      * {@inheritdoc}
      */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cynoinfotech_StorePickup::delete');
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getparam('entity_id');
        if ($id) {
            $name="";
            try {
                /** @var Cynoinfotech\Events\Model\Events $events  */
                $StorePickup = $this->storepickupFactory->create();
                $StorePickup->load($id);
                $name = $StorePickup->getName();
                $StorePickup->delete();
                $this->messageManager->addSuccess('The Store has been deleted.');
                $resultRedirect->setPath('storepickup/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('storepickup/*/edit', ['entity_id' =>$id ]);
                return $resultRedirect;
            }
        }
        $this->messageManager->addError(__('Store to delete was not found.'));
        $resultRedirect->setPath('storepickup/*/');
        return $resultRedirect;
    }
}
