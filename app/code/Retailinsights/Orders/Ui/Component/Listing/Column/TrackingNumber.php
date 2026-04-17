<?php
namespace Retailinsights\Orders\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
 
class TrackingNumber extends Column
{
 
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_customfactory;
    private $driverCollectionFactory;
    private $autoDriverCollection;
	protected $deliveryboy;
 
    public function __construct(
        \Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers\CollectionFactory $autoDriverCollection,
        \Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\CollectionFactory $driverCollectionFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\OrderFactory $orderFactory,
		\Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        array $components = [], array $data = [])
    {
        $this->autoDriverCollection = $autoDriverCollection;
        $this->driverCollectionFactory = $driverCollectionFactory;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
		$this->deliveryboy = $deliveryboy;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $connection  = $this->resource->getConnection();
            $tableName = $connection->getTableName('sales_shipment_grid'); 
            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->orderFactory->create()->loadByIncrementId($item["order_increment_id"]);
                // if($order->getCustcolumn()){
                    $driverData = $this->getDriverInformation($order);
                    $trackingNumber = $this->getOrderInfo($order);
                    $item['trackingNumber'] = html_entity_decode($driverData['trackingColumn']);
                    $item['dispatched_on'] = $trackingNumber['dispatched_on'];
                    // $item['trackingNumber'] = $order->getId();
                // }
            }
        }
        return $dataSource;
    }

    public function getDriverInformation($order)
    {
        // $labelText = [];
        $labelText['customColumn'] = '';
        $labelText['trackingColumn'] = '';
        $orderId = $order->getId();
        $drivers = $this->driverCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $orderId);
        if($drivers){
            if($drivers->getFirstItem()){
                
                 $driverId = $drivers->getFirstItem()->getData('driver_id');
                 $deliveryboyId = $drivers->getFirstItem()->getData('deliveryboy_id');
                 if(!empty($driverId)) {
                     $autodrivers = $this->autoDriverCollection->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('id', $driverId);
					 if($autodrivers->getFirstItem()->getData()) {
					  $info = $autodrivers->getFirstItem();
                        $labelText['customColumn'] = "<p>CBO Shipment</p><br/><p>Driver Name: ".$info->getData('driver_name')."</p><br>".
                        "<p>Driver Mobile: ".$info->getData('driver_mobile')."</p><br>".
                        "<p>Auto Number: ".$info->getData('auto_number')."</p><br>";
                        $labelText['trackingColumn'] = "<p>".$info->getData('driver_name')." : ".
                        $info->getData('driver_mobile')."</p><br> Auto:<p>".
                        $info->getData('auto_number')."</p>";
                       } elseif(!empty($drivers->getFirstItem()->getData('tracking_title'))){
						$name = $drivers->getFirstItem()->getData('tracking_title');
						$number = $drivers->getFirstItem()->getData('tracking_number');
						$labelText['customColumn'] = "<p>CBO Shipment</p><br/><p>Other Courier Name: ".$name."</p><br>".
							"<p>Number: ".$number."</p><br>";
                        $labelText['trackingColumn'] = "<p>".$name." : ".$number."</p>";
                        }

				       } elseif(!empty($deliveryboyId)) {
                        $deliveryboy = $this->deliveryboy->load($deliveryboyId);
						$deliveryboyName = $deliveryboy->getName();
						$deliveryboyContact = $deliveryboy->getMobileNumber();
						$deliveryvehicleNumber = $deliveryboy->getVehicleNumber();
                        $labelText['customColumn'] = "<p>CBO Shipment</p><br/><p>Driver Name: ".$deliveryboyName."</p><br>".
                        "<p>Driver Mobile: ".$deliveryboyContact."</p><br>".
                        "<p>Auto Number: ".$deliveryvehicleNumber."</p><br>";
                        $labelText['trackingColumn'] = "<p>".$deliveryboyName." : ".
                        $deliveryboyContact."</p><br> Auto:<p>".
                        $deliveryvehicleNumber."</p>";
				      }
            }
        }
        return $labelText;        
    }

    public function getOrderInfo($order)
    {
        $data['dispatched_on'] = '';
        $orderId = $order->getId();
        $drivers = $this->driverCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $orderId);
        if($drivers){
            $data['dispatched_on'] = $drivers->getFirstItem()->getData('created_at');
           
        }
        return $data;
    }

}