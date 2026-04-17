<?php

namespace Centralbooks\DeliveryAmount\Plugin;

use Magento\Sales\Controller\Adminhtml\Order\AddComment;
use Magento\Framework\App\Action\HttpPostActionInterface;

class ControllerOrderCommentPlugin extends \Magento\Sales\Controller\Adminhtml\Order implements HttpPostActionInterface
{

    protected $deliveryboyOrderFactory;
    protected $resultRedirectFactory;

    public function __construct(
	    \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory,
	    \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
	  )
    {
    
	    $this->deliveryboyOrderFactory = $deliveryboyOrderFactory;
	    $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
   	
  	  return afterExecute();
    }



    public function afterExecute(\Magento\Sales\Controller\Adminhtml\Order\AddComment $history, $result)
    {
		$oID = $history->getRequest()->getParam('order_id');
        	$oHistory = $history->getRequest()->getParam('history');
		$curentStatus =  $oHistory['status']; 
		$deliveryboyOrder = $this->deliveryboyOrderFactory->create()->load($oID,'order_id');
		if(!empty($deliveryboyOrder)){
			$deliveryboyOrder->setOrderStatus($curentStatus)->save();
			return $result;
		}
     }

}
