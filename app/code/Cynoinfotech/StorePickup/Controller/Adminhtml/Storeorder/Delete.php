<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Storeorder;

class Delete extends \Cynoinfotech\StorePickup\Controller\Adminhtml\StorePickupOrder
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
        return $this->_authorization->isAllowed('Cynoinfotech_StorePickup::storeorder_delete');
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getparam('entity_id');
        if ($id) {
            $name="";
            try {
                /** @var Cynoinfotech\Events\Model\Events $events  */
                $StorePickupOrder = $this->storepickuporderFactory->create();
                $StorePickupOrder->load($id);
                $name = $StorePickupOrder->getName();
                $StorePickupOrder->delete();
                $this->messageManager->addSuccess('The Store order has been deleted.');
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
