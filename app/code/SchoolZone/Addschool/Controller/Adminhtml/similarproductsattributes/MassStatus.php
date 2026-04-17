<?php
namespace SchoolZone\Addschool\Controller\Adminhtml\similarproductsattributes;

use Magento\Backend\App\Action;

class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Update blog post(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('similarproductsattributes');
		//echo '<pre>';print_r($itemIds);die;

        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('school_status');
				foreach ($itemIds as $postId) {
                    $post = $this->_objectManager->get('SchoolZone\Addschool\Model\Similarproductsattributes')->load($postId);
                    //$post->setIsActive($status)->save();
					 $post->setSchoolStatus($status)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        //return $this->resultRedirectFactory->create()->setPath('/addschool/similarproductsattributes');
		return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

}