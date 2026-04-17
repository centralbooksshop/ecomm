<?php
namespace Retailinsights\ProcessCBOOrders\Controller\Index;

class CheckShipmentId extends \Magento\Framework\App\Action\Action
{
	protected $orderManagement;
	protected $_pageFactory;
	public function __construct(
		\Magento\Sales\Api\OrderManagementInterface $orderManagement,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->orderManagement = $orderManagement;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		// $queries = array();
		// parse_str($_SERVER['QUERY_STRING'], $queries);


		// $orderId = $queries['id'];

		// if($this->orderManagement->cancel($orderId)){
		// 	echo 'order cancelled success';
		// }else{
		// 	echo 'order cancelled fails';
		// }


		return;//$this->_pageFactory->create();
	}
}