<?php 
namespace Retailinsights\Orders\Controller\Adminhtml\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class MassDelete
 */
class Bulkship extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
	 * @var Curl
     */
    protected $orderManagement;
    protected $collectionFactory;
    protected $resultRedirectFactory;
    protected $order;
    protected $curl;
    protected $date;
    protected $trackFactory;
    protected $adminSession;

	/**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $shipmentNotifier;
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
	  * @param \Magento\Sales\Model\Convert\Order $convertOrder
	 * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
	 * @param \Magento\Shipping\Model\ShipmentNotifier    $shipmentNotifier
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeconfig,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Sales\Model\Convert\Order $convertOrder,
        OrderManagementInterface $orderManagement,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Sales\Api\Data\OrderInterface $order,
		\Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
		\Magento\Backend\Model\Auth\Session $adminSession
    ) {
        parent::__construct($context, $filter);
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->_scopeConfig = $scopeconfig;
		$this->_orderRepository = $orderRepository;
		$this->_convertOrder = $convertOrder;
		$this->curl = $curl;
		$this->order = $order;
		$this->trackFactory = $trackFactory;
		$this->shipmentNotifier = $shipmentNotifier;
		$this->_messageManager = $messageManager;
		$this->date =  $date;
		$this->adminSession = $adminSession;
      }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countShipments = 0;
	$resultRedirect = $this->resultRedirectFactory->create();
	$shipmentLocation;
        $roleData = $this->adminSession->getUser()->getRole()->getData();
        //print_r($roleData,true);
        if( $roleData['role_name'] == "Maharashtra GST"){
                        $locationExplode = explode(' ',$roleData['role_name']);
                        $shipmentLocation = $locationExplode[0];
        }else if($roleData['role_name'] == "Telangana GST"){
                        $locationExplode = explode(' ',$roleData['role_name']);
                        $shipmentLocation = $locationExplode[0];
        }else{
                        $shipmentLocation = "Telangana";
        }
        
        foreach ($collection->getItems() as $order) 
		  {  
			  
			   // Allow only if order status is 'assigned_to_picker'
			if ($order->getStatus() !== 'assigned_to_picker') {
				$this->_messageManager->addErrorMessage(__('Order #%1 cannot be shipped because it is not in Assigned to Picker status.', $order->getIncrementId()));
				continue;
			}
			  if (!$order->getEntityId() || $order->hasShipments() || !$order->canShip()) {
				 continue;
			  }
			    //$entity_id = $order->getEntityId();
		        $shipmenttype = 'cboshipping';
                //$order = $this->_orderRepository->get($entity_id);
				// to check order can ship or not
				if (!$order->canShip()) {
				 throw new \Magento\Framework\Exception\LocalizedException(__('You cant create the Shipment of this order.') );
				}

				$orderShipment = $this->_convertOrder->toShipment($order);

				foreach ($order->getAllItems() AS $orderItem) {

				// Check virtual item and item Quantity
				if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
				continue;
				}

				$qty = $orderItem->getQtyToShip();
				$shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qty);
				$orderShipment->addItem($shipmentItem);
				}

				$orderShipment->register();
				$orderShipment->getOrder()->setIsInProcess(true);

				try {
					// Save created Order Shipment
					$orderShipment->save();
					$orderShipment->getOrder()->save();
					//$shipmentId = $orderShipment->getIncrementId();
                    // Send Shipment Email
					$this->shipmentNotifier->notify($orderShipment);
					//$order->setData('canceled_shipment',2); 
					//$orderShipment->save();
					$order->setState('complete');
					$order->setStatus('complete');
					$order->setData('shipment_type',$shipmenttype);
					$order->setData('shipment_location', $shipmentLocation);
					$order->save();

				} catch (\Exception $e) {
					throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
				}

				$countShipments++;
	    }
			$countFailedShipments = $collection->count() - $countShipments;

			if ($countFailedShipments && $countShipments) {
				$this->_messageManager->addErrorMessage(__('%1 order(s) were not shipped through CBO Shipment.', $countFailedShipments));
			} elseif ($countFailedShipments) {
				$this->_messageManager->addErrorMessage(__('No order(s) were shipped through CBO Shipment.'));
			}

			if ($countShipments) {
				$this->_messageManager->addSuccessMessage(__('You have shipped through CBO Shipment %1 order(s).', $countShipments));
			}

			return $resultRedirect->setPath('sales/order/');
        }
     }
