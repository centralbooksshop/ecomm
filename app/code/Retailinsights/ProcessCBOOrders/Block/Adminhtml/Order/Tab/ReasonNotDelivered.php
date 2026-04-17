<?php
namespace Retailinsights\ProcessCBOOrders\Block\Adminhtml\Order\Tab;
 
class ReasonNotDelivered extends \Magento\Backend\Block\Template implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'order/reason_not_delivered.phtml';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResColF;

   public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Retailinsights\ProcessCBOOrders\Model\ResourceModel\DeliveredCBOOrders\Collection $deliveryboyOrderResColF,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->timezone = $timezone;
        $this->_coreRegistry = $registry;
        $this->orderFactory = $orderFactory;
        $this->deliveryboyOrderResColF = $deliveryboyOrderResColF;
        parent::__construct($context, $data);
    }
    
    /**
     * Get Order From Registry.
     *
     * @return \Magento\Sales\Api\OrderInterface
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }


	public function getOrderStatus()
    {
        return $this->getOrder()->getStatus();
    }

    /**
     * GEt ORder Id.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Get ORder INcrement Id.
     *
     * @return int
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

	 public function getDriverId()
     {
        return $this->getDeliveryboyOrder()->getDriverId();
     }

    /**
     * GEt Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Add Reason for Driver Not Delivered Order');
    }

    /**
     * Get Tab Title.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Add Reason for Driver Not Delivered Order');
    }

    /**
     * Get bool flag for toggling tab.
     *
     * @return bool
     */
    public function canShowTab()
    {
        $order = $this->orderFactory->create()->loadByIncrementId($this->getOrderIncrementId());
        
        $allowedShipping = explode(",", $this->helperData->getAllowedShipping());
        if (!in_array($order->getShippingMethod(), $allowedShipping)) {
            
            return false;
        }
		//if (!$this->getDeliveryboyOrder()->getId()) {
		if (!$this->getDeliveryboyOrder()->getDriverId()) {
            return false;
        }
        return true;
    }

    /**
     * Is Tab Hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get Deliveyrboy Order.
     *
     * @return void
     */
    public function getDeliveryboyOrder()
    {
        $collection = $this->deliveryboyOrderResColF
            ->addFieldToFilter(
                "order_id",
                $this->getOrderId()
            );
        /*$this->_eventManager->dispatch(
            'wk_deliveryboy_assigned_order_collection_apply_filter_event',
            [
                'deliveryboy_order_collection' => $collection,
                'collection_table_name' => 'main_table',
                'owner_id' => 0,
            ]
        );*/
        $deliveryBoyOrder = $collection->getFirstItem();
        return $deliveryBoyOrder;
    }
}
