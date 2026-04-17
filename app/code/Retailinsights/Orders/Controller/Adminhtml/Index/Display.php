<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Index;

use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;

class Display extends \Magento\Framework\App\Action\Action
{   protected $ProcessDelhiveryOrdersFactory;
    protected $ProcessCBOOrdersFactory;
    protected $resultPageFactory = false;
    protected $itemFactory;
    protected $adminSession;
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
	protected $userContext;
	protected $userFactory;
	protected $variable;

    public function __construct(
        \Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory $ProcessCBOOrdersFactory,
       \Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
	    \Ecom\Ecomexpress\Model\ResourceModel\Awb\CollectionFactory $ecomcollectionFactory,
        \Psr\Log\LoggerInterface $logger,
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
		UserContextInterface $userContext,
		\Magento\User\Model\UserFactory $userFactory,
		\Magento\Variable\Model\Variable $variable,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Backend\Model\Auth\Session $adminSession,
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
        $this->collectionFactory = $collectionFactory;
		$this->ecomcollectionFactory = $ecomcollectionFactory;
		$this->logger = $logger;
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
		$this->userContext = $userContext;
		$this->userFactory = $userFactory;
		$this->variable = $variable;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderManagement = $orderManagement;
        $this->cancelShipment = $cancelShipment;
        $this->registry = $registry;
        $this->context = $context;
        $this->adminSession = $adminSession;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }


    public function execute()
    {
        $queries = array();
        parse_str($_SERVER['QUERY_STRING'], $queries);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $core_config_data_tableName = $resource->getTableName('core_config_data');
        $core_config_data_sql = $connection->select()->from($core_config_data_tableName)->where('path = ?', 'admin/url/custom');
        $core_config_data_result = $connection->fetchRow($core_config_data_sql);  
        $admin_url = $core_config_data_result['value'];
        $return_url = $admin_url.'cbsadmin/sales/order';

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($return_url);
        $key = $queries['key'];
        $orderId = $queries['id'];
     
		$orderdetails = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		$order_storeId  = $orderdetails->getStoreId();
		if($order_storeId == 1) {
			if($key == 'shipping') {
				if(!empty($orderId)) {
					$admin_userId = $this->userContext->getUserId();
					$admin_user = $this->userFactory->create()->load($admin_userId);
					$admin_role = $admin_user->getRole();
					$admin_role_id = $admin_role->getRoleId();
					if(!empty($admin_role_id)) {
						$hyderabad_roleids_variable = $this->variable->loadByCode('hyderabad_roleids', 'admin');
						$hyderabad_roleids_string = $hyderabad_roleids_variable->getPlainValue();
						$hyderabad_roleids = explode(",", $hyderabad_roleids_string);

						$mumbai_roleids_variable = $this->variable->loadByCode('mumbai_roleids', 'admin');
						$mumbai_roleids_string = $mumbai_roleids_variable->getPlainValue();
						$mumbai_roleids = explode(",", $mumbai_roleids_string);

						if (in_array($admin_role_id, $hyderabad_roleids)) {
							$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
							$order->setData('location_code', 'Telangana'); 
							$order->save();
						} elseif (in_array($admin_role_id, $mumbai_roleids)) {
							$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
							$order->setData('location_code', 'Maharashtra'); 
							$order->save();
						}
					}
				}
			} elseif($key == 'cancel_shipment'){
                if(!empty($orderId)) {
					$admin_userId = $this->userContext->getUserId();
					$admin_user = $this->userFactory->create()->load($admin_userId);
					$admin_role = $admin_user->getRole();
					$admin_role_id = $admin_role->getRoleId();
					if(!empty($admin_role_id)) {
						$hyderabad_roleids_variable = $this->variable->loadByCode('hyderabad_roleids', 'admin');
						$hyderabad_roleids_string = $hyderabad_roleids_variable->getPlainValue();
						$hyderabad_roleids = explode(",", $hyderabad_roleids_string);

						$mumbai_roleids_variable = $this->variable->loadByCode('mumbai_roleids', 'admin');
						$mumbai_roleids_string = $mumbai_roleids_variable->getPlainValue();
						$mumbai_roleids = explode(",", $mumbai_roleids_string);

						if (in_array($admin_role_id, $hyderabad_roleids)) {
							$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
							$order->setData('location_code', ''); 
							$order->save();
						} elseif (in_array($admin_role_id, $mumbai_roleids)) {
							$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
							$order->setData('location_code', ''); 
							$order->save();
						}
					}
				}
			}
		}

        $shipping_method = $queries['value'];
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

                $shipmentLocation;
                $roleData = $this->adminSession->getUser()->getRole()->getData();
                if( $roleData['role_name'] == "Maharashtra GST"){
                        $locationExplode = explode(' ',$roleData['role_name']);
                        $shipmentLocation = $locationExplode[0];
                }else if($roleData['role_name'] == "Telangana GST"){
                        $locationExplode = explode(' ',$roleData['role_name']);
                        $shipmentLocation = $locationExplode[0];
		}else{
			$shipmentLocation = "Telangana";
		}
            if($shipping_method == 'centralbooksshipping'){
                $return_url = $admin_url.'cbsadmin/sales/order';
            }
                $resultRedirect->setPath($return_url);
            
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
				if (!$order->canShip()) {
					throw new \Magento\Framework\Exception\LocalizedException(
						__("You can't create the Shipment of this order.")
					);
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
                    if($shipment->save()){
                        $this->logger->info('Inside shipment save');
                        $shipment->getOrder()->save();
                        $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                        ->notify($shipment);
                        $order->setData('canceled_shipment',2); 
                        $order->save();
                        //$this->saveShipmentType($orderId, 'cboshipping');
                        $this->saveShipmentType($orderId, 'cboshipping', $shipmentLocation);
                        $this->messageManager->addSuccess(__("Order Shipment Success"));
                        
                    } else {
                        $this->messageManager->addError(__("Can't Ship this Order"));
                    }
                } catch (\Exception $e) {

                   //echo '<pre>'; print_r($e);
                    $this->messageManager->addError($e->getMessage());
                    return $resultRedirect;
                }
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
    
                $invoice_url =  $admin_url.'cbsadmin/sales/order_invoice/print/invoice_id/'.$invoice_id.'/key/';
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

		if ($key == 'cancel_shipment') {
			$this->registry->unregister('isSecureArea');
			$this->registry->register('isSecureArea', true);

			$shipmentId = $this->getShipmentIdByOrderId($orderId);
			$shipment = $this->cancelShipment->get($shipmentId);
			$deleted = $shipment ? $this->cancelShipment->delete($shipment) : false;

			if ($deleted) {
				$order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);

				// Delete from ProcessCBOOrders
				$processOrders = $this->ProcessCBOOrdersFactory->create()->getCollection();
				foreach ($processOrders as $record) {
					if ($record->getData('order_id') == $orderId) {
						$record->delete();
					}
				}

				// Clear Delhivery AWB details
				$delhiveryRecords = $this->collectionFactory->create();
				foreach ($delhiveryRecords as $record) {
					if ($record->getData('orderid') == $orderId) {
						$record->delete();
					}
				}

				// Update Order Flags and Status
				$order->setData('canceled_shipment', 1);
				$order->setData("cbo_courier_name", null);
				$order->setData("cbo_reference_number", null);
				$order->save();

				$this->ResetQtyShipped($orderId);
				$this->changeOrderStatus($orderId);

				$this->messageManager->addSuccessMessage(__('Shipment cancellation successful.'));
			} else {
				$this->messageManager->addErrorMessage(__('Unable to cancel shipment.'));
			}

			return $resultRedirect;
		}
        return;

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
        $sql = "UPDATE sales_order SET state = 'processing', status = 'assigned_to_picker' WHERE entity_id = " . $orderId;
        $connection->query($sql);
        
        $sqlSales = "UPDATE sales_order_grid  SET status = 'assigned_to_picker' WHERE entity_id = " . $orderId;
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

    public function saveShipmentType($orderId, $shipmentType, $shipmentLocation)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                $order->setData('shipment_type',$shipmentType); 
                $order->setData('shipment_location', $shipmentLocation);
                $order->save();
    }
}
