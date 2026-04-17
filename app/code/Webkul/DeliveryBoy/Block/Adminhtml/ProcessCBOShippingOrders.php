<?php
namespace Webkul\DeliveryBoy\Block\Adminhtml;
use Webkul\DeliveryBoy\Model\Deliveryboy\Source\ApproveStatus;
class ProcessCBOShippingOrders extends \Magento\Framework\View\Element\Template
{
	protected $_coreRegistry;
	protected $_postFactory;
	public $_storeManager;
	protected $_customerSession;	
	protected $deliveryboyResourceCollectionFactory;
    	protected $deliveryboyOrderResourceCollectionFactory;
	protected $scopeConfig;
    	const XML_PACKAGE_TYPE =       'deliveryboy/configuration/packagetype';
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Registry $coreRegistry,
		 \Magento\Customer\Model\Session $session,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Backend\Block\Widget\Context $context,
		\Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollectionFactory,
        	\Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory,
        	\Webkul\DeliveryBoy\Helper\DeliveryAutomation $deliveryAutomationHelper,
		array $data = []
		
	)
	{	
		$this->scopeConfig = $scopeConfig;
		$this->_coreRegistry = $coreRegistry;
		$this->_customerSession = $session;
		$this->_storeManager=$storeManager;
		$this->deliveryboyResourceCollectionFactory = $deliveryboyResourceCollectionFactory;
        	$this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
        	$this->deliveryAutomationHelper = $deliveryAutomationHelper;

        	parent::__construct($context, $data);
	}


	/**
     * Get Deliveryboy Object List.
     *
     * @return array
	 */

    public function getDeliveryBoyList()
    {
        $deliveryboyCollection = $this->deliveryboyResourceCollectionFactory->create();

        $deliveryboyCollection->addFieldToFilter("status", 1)
            ->addFieldToFilter("approve_status", ApproveStatus::STATUS_APPROVED)
            ->addFieldToFilter("availability_status", 1)
            ->setOrder("created_at", "ASC");
        $this->_eventManager->dispatch(
            'wk_deliveryboy_deliveryboy_collection_apply_filter_event',
            [
                'deliveryboy_collection' => $deliveryboyCollection,
                'collection_table_name' => 'main_table',
            ]
        );
        $deliveryboyList = [];
        if ($this->deliveryAutomationHelper->isSortDeliveryBoyNearestDistanceEnabled()) {
            $orderId = $this->getOrderId();
            $nearestSortedDeliveryBoy = $this->deliveryAutomationHelper->sortDeliveryBoyDataWithDistances(
                $orderId,
                $deliveryboyCollection
            );

            $distanceUnit = $this->deliveryAutomationHelper->getDistanceUnit();
            foreach ($nearestSortedDeliveryBoy as $delboy) {
                $eachDeliveryboy = [];
                $eachDeliveryboy["id"] = $delboy['deliveryboy']->getId();
                $name = $delboy["deliveryboy"]->getName();
                $distance = $delboy['distance'];
                $eachDeliveryboy['name'] =
                $this->deliveryAutomationHelper->formatDeliveryboyName($name, $distance);
                $eachDeliveryboy["status"] = $delboy['deliveryboy']->getStatus();
                $eachDeliveryboy[
                    "availabilityStatus"
                ] = (bool)$delboy['deliveryboy']->getAvailabilityStatus();
                $deliveryboyList[] = $eachDeliveryboy;
            }
        } else {
            foreach ($deliveryboyCollection as $each) {
                $eachDeliveryboy = [];
                $eachDeliveryboy["id"] = $each->getId();
                $eachDeliveryboy["name"] = $each->getName();
                $eachDeliveryboy["status"] = $each->getStatus();
                $eachDeliveryboy[
                    "availabilityStatus"
                ] = (bool)$each->getAvailabilityStatus();
                $deliveryboyList[] = $eachDeliveryboy;
            }
        }
        return $deliveryboyList;
    }

	public function getPackageData()
    {
        $storeScope  = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $packageType = $this->scopeConfig->getValue(self::XML_PACKAGE_TYPE, $storeScope);
        $pData 	     = explode(";", $packageType);

        foreach ($pData as $pType) {
            $pType = trim($pType);
            if (!empty($pType)) {

                    if($pType == "Box"){
                            echo "<option value='$pType'>$pType</option>";
                    }else{
                        echo "<option value='$pType'>$pType</option>";
                }
            }
        }
   }

	/*public function getAutoDrivers()
	{
		$driverCollection = $this->deliveryboyResourceCollectionFactory->create(); 
        $filter = $driverCollection->getCollection();

		$driverFilterData =  $filter->getData();
		return $driverFilterData;
	}*/
}
