<?php


namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Assign;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
/**
 * Class Awb
 */
class Fetch extends \Magento\Backend\App\Action {
	
	/**
	 *
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	//protected $resultPageFactory;
	/**
	 *
	 * @param \Magento\Framework\App\Action\Context $context        	
	 * @param
	 *        	\Magento\Framework\View\Result\PageFactory resultPageFactory
	 */
	/*public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
		parent::__construct ( $context );
		$this->resultPageFactory = $resultPageFactory;
	}*/
	/**
	 * Default Ecomexpress AWB page
	 *
	 * @return void
	 */
	public function execute() { 

		$orderId = $this->getRequest()->getParam('order');
		$order = $this->_objectManager->get("\Magento\Sales\Model\Order")->load($orderId);
		$address = $order->getShippingAddress();
		$zipcodeCollection = $this->_objectManager->create ( 'Ecom\Ecomexpress\Model\Pincode' )->load($address->getPostcode(),'pincode');
		if(count($zipcodeCollection->getData())){
			$payment = $order->getPayment()->getMethodInstance()->getCode();
			$pay_type = 'PPD';
			if($payment == 'cashondelivery' || $payment == 'phoenix_cashondelivery' || $payment == 'mst_cashondelivery')
				$pay_type = 'COD';
			$model = $this->_objectManager->create ( 'Ecom\Ecomexpress\Model\Awb' )->getCollection()
				->addFieldToFilter('state',0)->addFieldToFilter('awb_type',$pay_type);
			if(count($model->getData())){
				$awb = $model->getFirstItem()->getAwb();
				echo $awb;//die;
				//return $awb;
			}else{
				echo $pay_type.' AWB number is not available';
			}
		}else{
			echo 'Pincode is not serviceable';
		}
    }
}