<?php
namespace Retailinsights\Orders\Controller\Index;

use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;

class Display extends \Magento\Framework\App\Action\Action
{
    protected $ProcessCBOOrdersFactory;
    protected $resultPageFactory = false;
    protected $itemFactory;

    protected $resultRedirectFactory;
    protected $postFactory;
    protected $orderRepository;
    protected $shipmentFactory;
    protected $shipmentRepository;
    public $_storeManager;
    protected $formKey;
    protected $customerSession;
    protected $customerrepository;
    protected $order;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $invoiceRepository;
    protected $searchCriteriaBuilder;
     /**
     * @param Context                                     $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Convert\Order          $convertOrder
     * @param \Magento\Shipping\Model\ShipmentNotifier    $shipmentNotifier
     */
    protected $orderManagement;
    protected $urlBuilder;
	protected $logger;

    public function __construct(
        \Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory $ProcessCBOOrdersFactory,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Retailinsights\Orders\Model\PostFactory $postFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerRepositoryInterface $customerrepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $cancelShipment,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
        \Magento\Sales\Api\Data\OrderInterface $order
    )
    {
        $this->ProcessCBOOrdersFactory = $ProcessCBOOrdersFactory;
        $this->order = $order;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->itemFactory = $itemFactory;
        $this->backendUrlManager = $backendUrlManager;
        $this->urlBuilder = $urlBuilder;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->postFactory = $postFactory;
        $this->orderRepository = $orderRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->formKey = $formKey;
        $this->_storeManager=$storeManager;
        $this->customerSession = $customerSession;
        $this->customerrepository = $customerrepository; 
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderManagement = $orderManagement;
		$this->logger = $logger;
        $this->cancelShipment = $cancelShipment;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }


    public function execute()
    {
       $queries = array();
        parse_str($_SERVER['QUERY_STRING'], $queries);
        $url1= $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $return_url = $url1.'cbsadmin/sales/order';
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($return_url);

        $orderId = $queries['id'];
        $shipping_method = $queries['value'];
        $key = $queries['key'];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $state = $objectManager->get('Magento\Framework\App\State');
        $this->logger->info('current Area code - '.$state->getAreaCode()); 

        if($key == 'cancel_order'){
            if($this->orderManagement->cancel($orderId)){
                 $this->messageManager->addSuccess(__("Order Cancellation Success"));
                return $resultRedirect;
            }else{
                $this->messageManager->addError(__("Can't Cancel this Order"));
                return $resultRedirect;
            }
        }

        if($key == 'shipping'){
           
            if($shipping_method == 'centralbooksshipping'){
                $return_url = $url1.'cbsadmin/sales/order';
            }
            $resultRedirect->setPath($return_url);
            
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            
                if (! $order->canShip()) {
                     die();
                }
                $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
                $shipment = $convertOrder->toShipment($order);
                foreach ($order->getAllItems() AS $orderItem) {
                    if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }

                    $qtyShipped = $orderItem->getQtyToShip();
                    $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                    $shipment->addItem($shipmentItem);
                }
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);
                try {
                   // $shipment->save();
                    if($shipment->save()){
                        $this->logger->info('Inside shipment save');
                        $shipment->getOrder()->save();
                    $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                        ->notify($shipment);
                        $order->setData('canceled_shipment',2); 
                        $order->save();
                        $this->saveShipmentType($orderId, 'cboshipping');
                        $this->messageManager->addSuccess(__("Order Shipment Success"));
                        
                    }else{
                        $this->messageManager->addError(__("Can't Ship this Order"));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError(__("Can't Ship this Order"));
                    return $resultRedirect;
                }
                /*if($shipping_method == 'fedexlabel'){
                    $this->saveShipmentType($orderId, 'fedex');
                    $this->logger->info('Inside fedexlabel');
                    $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                    $shipment = $order->getShipmentsCollection()->getFirstItem();
                    $shipmentId = $shipment->getId();
                    $return_url = $this->backendUrlManager->getUrl('infomodus_fedexlabel/items/edit', 
                   ['order_id' => $orderId, 'direction' => 'shipment', 'redirect_path' => 'order']);
                    $this->logger->info('fedex return url - '.$return_url);
                    $resultRedirect->setPath('infomodus_fedexlabel/items/edit',['order_id' => $orderId, 'direction' => 'shipment', 'redirect_path' => 'order']);
               }*/

			   if($shipping_method == 'delhivery'){
                    $this->saveShipmentType($orderId, 'delhivery');
                    $this->logger->info('Inside delhiverylabel');
                }

				if($shipping_method == 'ecomexpress'){
                    $this->saveShipmentType($orderId, 'ecomexpress');
                    $this->logger->info('Inside ecomexpresslabel');
                }
			   
            return $resultRedirect;
        }

        if($key == 'invoice_id'){
            $invoice_id = $queries['invoice_id'];
            
            $collection= $this->postFactory->create()->getCollection();
                $count = 0;
                $id = 0; 
                foreach ($collection as  $value) {
                        if($value['invoice_id'] == $invoice_id){
                            $id = $value['id'];
                            $count =  $value['invoice_count'] + 1;
                        }else{
                            $id = 0;
                        }
                }
            
            if($invoice_id!=''){
                $object = \Magento\Framework\App\ObjectManager::getInstance();
                $url = $object->get(\Magento\Backend\Model\UrlInterface::class);
    
                $invoice_url =  $url1.'cbsadmin/sales/order_invoice/print/invoice_id/'.$invoice_id.'/key/';
                // echo $url->getSecretKey();
                if($this->_redirect($invoice_url.$url->getSecretKey())){

                    if($id > 0){

                         $mandeetotcol = $this->postFactory->create();
                         $mandeetotcol->setId($id);
                         $mandeetotcol->setInvoiceCount($count);
                         if($mandeetotcol->save()){

                         }

                    }else{
                         $mandeetotcol = $this->postFactory->create();

                         $mandeetotcol->setOrderId($orderId);
                         $mandeetotcol->setInvoiceId($invoice_id);
                         $mandeetotcol->setInvoiceCount(1);
                         if($mandeetotcol->save()){

                         
                         }
                    }
                }  
                
            } else{
                echo 'cant ship this order';
            }

        }
        // cancel shipment
        if($key == 'cancel_shipment'){
            $deleteShipment = false;

            if($this->registry->registry('isSecureArea')){
                $this->registry->unregister('isSecureArea');
            }
            $this->registry->register('isSecureArea', true);

            
            $shipmentId = $this->getShipmentIdByOrderId($orderId);

            $shipmentItemData = $this->cancelShipment->get($shipmentId);
            if ($shipmentItemData) {
                $deleteShipment = $this->cancelShipment->delete($shipmentItemData);
            }
            if($deleteShipment){
                // change order status
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                $model = $this->ProcessCBOOrdersFactory->create()->getCollection();
                foreach ($model as $value) {
                    if($value['order_id'] == $orderId){
                        $value->delete();
                    }
                }

                $order->setData('canceled_shipment',1); 
                $order->save();
                $this->ResetQtyShipped($orderId);
                $this->changeOrderStatus($orderId);
                $this->messageManager->addSuccess(__("Shipment Cancellation Success"));
            }else{
                $this->messageManager->addSuccess(__("Unable to cancel shipment"));
            }
            return $resultRedirect;
        }
        return;//$this->_pageFactory->create();

    }
    public function getShipmentIdByOrderId(int $orderId)
    {
        // check for multiple shipments
        $shipmentRecords = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $shipments = $this->cancelShipment->getList($searchCriteria);

            if($shipments){
                $Records = $shipments->getFirstItem()->getData();
                $shipmentRecords = $Records['entity_id'];
            }
        } catch (Exception $exception)  {
            $this->logger->critical($exception->getMessage());
           
        }
        return $shipmentRecords;
    }

    function changeOrderStatus($orderId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sql = "UPDATE sales_order SET state = 'processing', status = 'processing' WHERE entity_id = " . $orderId;
        $connection->query($sql);
        
        $sqlSales = "UPDATE sales_order_grid  SET status = 'processing' WHERE entity_id = " . $orderId;
        $connection->query($sqlSales);
    }

    public function ResetQtyShipped($orderId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $order = $this->orderRepositoryInterface->get($orderId);
        
        foreach ($order->getAllVisibleItems() as $item) {
            $itemId = $item->getItemId();
            $sql = "UPDATE sales_order_item SET qty_shipped = 0 WHERE item_id = " . $itemId;
            $connection->query($sql);
        }

    }

    public function saveShipmentType($orderId, $shipmentType)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                $order->setData('shipment_type',$shipmentType); 
                $order->save();
    }
}