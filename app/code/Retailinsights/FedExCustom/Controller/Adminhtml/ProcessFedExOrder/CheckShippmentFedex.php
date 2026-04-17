<?php
 
namespace Retailinsights\FedExCustom\Controller\Adminhtml\ProcessFedExOrder;
 
// use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\ShipmentRepositoryInterface;
// use Magento\Framework\Controller\Result\JsonFactory;
 
class CheckShippmentFedex extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $orderRepository;
private $searchCriteriaBuilder;
    private $invoiceCountCollection;
    private $fedexLabels;

    public function __construct(
        \Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory $fedexLabels,
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
        $this->invoiceResourceCountCollection = $invoiceResourceCountCollection;
        $this->invoiceCountCollection = $invoiceCountCollection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
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

            if(empty($shipment->getData())){
                return 'no';
            }
            $flag = $this->checkPayslipGenerated($orderInfo);
            if($flag == 'no'){
                return 'no_pay_slip_generated';
            }
            $isLabel = $this->checkFedexLabel($orderInfo);
            if($isLabel == 'no'){
                return 'no_tracking_info';
            }
            return 'yes';
        }else{
            return 'no';
        }
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
                ->addFieldToFilter('invoice_id', $invoice_id);
        if($collection){
            foreach ($collection as  $value) {
                $invoice = trim($value['invoice_id']);
                $invoice_id = trim($invoice_id);
    
                if($invoice == $invoice_id){
                    return 'yes';
                }else{
                    return 'no';
                }
            }       
        }
        return 'no';
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
}
