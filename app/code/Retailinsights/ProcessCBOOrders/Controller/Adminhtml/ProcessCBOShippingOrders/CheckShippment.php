<?php
 
namespace Retailinsights\ProcessCBOOrders\Controller\Adminhtml\ProcessCBOShippingOrders;
 
// use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\ShipmentRepositoryInterface;
// use Magento\Framework\Controller\Result\JsonFactory;
 
class CheckShippment extends \Magento\Backend\App\Action
{
    private $fedexLabels;
    protected $_resultPageFactory;
    protected $orderRepository;
    private $searchCriteriaBuilder;
    private $invoiceCountCollection;

    public function __construct(
        \Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory $fedexLabels,
		\Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
		\Magento\Sales\Model\ResourceModel\Order\Grid\Collection $dtdccollectionFactory,
		\Ecom\Ecomexpress\Model\ResourceModel\Awb\CollectionFactory $ecomcollectionFactory,
        \Retailinsights\Orders\Model\PostFactory $invoiceCountCollection,
        \Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory $invoiceResourceCountCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        // \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context
    )
    {
        $this->fedexLabels = $fedexLabels;
		$this->collectionFactory = $collectionFactory;
		$this->dtdccollectionFactory = $dtdccollectionFactory;
		$this->ecomcollectionFactory = $ecomcollectionFactory;
        $this->invoiceResourceCountCollection = $invoiceResourceCountCollection;
        $this->invoiceCountCollection = $invoiceCountCollection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct($context);
    }
 
    public function execute()
    {
        $orderIds = $this->getRequest()->getPost('orderIds');
        $orderIds= array_map('trim', $orderIds);
        $orderIds = array_unique($orderIds);

        foreach($orderIds as $IncrementId){
            $result[$IncrementId] = $this->getOrderStatus(trim($IncrementId));
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }

    public function getOrderStatus($orderIncrementId)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementId);

        $status = $orderInfo->getStatus();
        $orderId = $orderInfo->getId();
        
        if(!$orderId){
            return 'no order id';
        }
        
        if($status == "dispatched_to_courier"){
            return 'dispatched_to_courier';
        }elseif($status == "order_delivered"){
            return 'order_delivered';
        }elseif(($status == 'complete') || ($status == 'pending') || ($status == 'processing')){
            $order = $this->orderRepository->get($orderId);
            $shipment = $order->getShipmentsCollection();
           
            $isLabel = $this->checkFedexLabel($orderInfo);
            if($isLabel == 'yes'){
                return 'no_tracking_info';
            }

			$isDelhiveryLabel = $this->checkDelhiveryLabel($orderInfo);
            if($isDelhiveryLabel == 'yes'){
                return 'no_tracking_info';
            }

			$isDtdcLabel = $this->checkDtdcLabel($orderInfo);
			if ($isDtdcLabel == 'yes') {
				return 'no_tracking_info';
			}

			$isEcomLabel = $this->checkEcomLabel($orderInfo);
            if($isEcomLabel == 'yes'){
                return 'no_tracking_info';
            }

            if(empty($shipment->getData())){
                return 'no';
            }
            $flag = $this->checkPayslipGenerated($orderInfo);
            if($flag == 'no'){
                return 'no_pay_slip_generated';
            }
            return 'yes';
        }else{
            return 'no';
        }
    }
    
    public function checkFedexLabel($order)
    {
        $orderId = $order->getId();
        $collection = $this->fedexLabels->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('lstatus', '0');
        if(!empty($collection->getFirstItem()->getData('order_id'))){
            return 'yes';
        }
        return 'no';
    }


	public function checkDelhiveryLabel($order)
    {
        $orderId = $order->getId();
        $collection = $this->collectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('orderid', $orderId);
           // ->addFieldToFilter('lstatus', '0');
        if(!empty($collection->getFirstItem()->getData('orderid'))){
            return 'yes';
        }
        return 'no';
    }

	public function checkDtdcLabel($order)
	{
		$orderId = $order->getId();

		/** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
		$collection = $this->orderCollectionFactory->create()
			->addFieldToSelect(['entity_id', 'increment_id', 'cbo_reference_number'])
			->addFieldToFilter('entity_id', $orderId);

		$orderData = $collection->getFirstItem();

		// Debug line (optional — use logging instead of echo in production)
		 //echo '<pre>'; print_r($orderData->getData()); die;

		if (!empty($orderData->getData('cbo_reference_number'))) {
			return 'yes';  // Label exists for DTDC
		}

		return 'no'; // No DTDC label yet
	}



	public function checkEcomLabel($order)
    {
        $orderId = $order->getId();
        $collection = $this->ecomcollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('orderid', $orderId);
           // ->addFieldToFilter('lstatus', '0');
        if(!empty($collection->getFirstItem()->getData('orderid'))){
            return 'yes';
        }
        return 'no';
    }

    public function checkPayslipGenerated($order)
    {  
        $orderId = $order->getId();
        $invoice_id = '';
        foreach ($order->getInvoiceCollection() as $invoice)
        {
            $invoice_id = $invoice->getId();
        }
        $collection = $this->invoiceResourceCountCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $orderId);
        // if($collection){
        if($collection->getFirstItem()->getData('order_id')){
            if($collection->getFirstItem()->getData('invoice_count') > 0){
                return 'yes';            
            }else{
                return 'no';
            }
        }else{
            return 'no';
        }
            // foreach ($collection as  $value) {
            //     $invoice = trim($value['invoice_id']);
            //     $invoice_id = trim($invoice_id);
    
            //     if($invoice == $invoice_id){
            //         return 'yes';
            //     }else{
            //         return 'no';
            //     }
            // }       
        // }
        return 'no';
    }
}
