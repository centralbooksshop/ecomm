<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block\Adminhtml\Order\Tab;

use Webkul\DeliveryBoy\Model\Deliveryboy\Source\ApproveStatus;

class AssignDeliveryBoy extends \Magento\Backend\Block\Template implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $_template = 'order/assign_deliveryboy.phtml';

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     */
    protected $deliveryboyResourceCollectionFactory;

    /**
     * @var $deliveryboyOrderResourceCollectionFactory
     */
    protected $deliveryboyOrderResourceCollectionFactory;

    /**
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Deliveryboy\CollectionFactory $deliveryboyResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Helper\DeliveryAutomation $deliveryAutomationHelper
     * @param array $data
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory,
        \Webkul\DeliveryBoy\Helper\DeliveryAutomation $deliveryAutomationHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_coreRegistry = $registry;
        $this->jsonHelper = $jsonHelper;
        $this->orderFactory = $orderFactory;
        $this->deliveryboyResourceCollectionFactory = $deliveryboyResourceCollectionFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
        $this->deliveryAutomationHelper = $deliveryAutomationHelper;

        parent::__construct($context, $data);
    }

    /**
     * Encode Data in Json.
     *
     * @param array $configData
     * @return string
     */
    public function jsonEncode($configData)
    {
        return $this->jsonHelper->jsonEncode($configData);
    }

    /**
     * Get Current Order.
     *
     * @return \Magento\Sales\Api\OrderInterface
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * GEt Order Id.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Get Current Order Id.
     *
     * @return int
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

	public function getOrderStatus()
    {
        return $this->getOrder()->getStatus();
    }

	

    /**
     * Get Tab Label.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Assign DeliveryBoy');
    }

    /**
     * Get Tab Title.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Assign DeliveryBoy');
    }

    /**
     * Return bool flag for toggling Tab.
     *
     * @return bool
     */
    public function canShowTab()
    {
        $order = $this->orderFactory->create()->loadByIncrementId(
            $this->getOrderIncrementId()
        );

        $allowedShipping = explode(
            ",",
            $this->helper->getAllowedShipping()
        );
        if (!in_array(
            $order->getShippingMethod(),
            $allowedShipping
        )
        ) {
            return false;
        }
        if (!$this->getDeliveryboyOrder()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * Check tab hidden status.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
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

    /**
     * Get Currently Assigned Deliveyrboy.
     *
     * @return int
     */
    public function getAssignedDeliveryBoy()
    {
        $deliveryBoyOrder = $this->getDeliveryboyOrder();
        if ($deliveryBoyOrder->getId()) {
            return $deliveryBoyOrder->getDeliveryboyId() ?? 0;
        }
        return 0;
    }

    /**
     * Get Deliveryboy Order.
     *
     * @return void
     */
    public function getDeliveryboyOrder()
    {
        $collection = $this->deliveryboyOrderResourceCollectionFactory->create()
            ->addFieldToFilter(
                "increment_id",
                $this->getOrderIncrementId()
            );
        $this->_eventManager->dispatch(
            'wk_deliveryboy_assigned_order_collection_apply_filter_event',
            [
                'deliveryboy_order_collection' => $collection,
                'collection_table_name' => 'main_table',
                'owner_id' => 0,
            ]
        );
        $deliveryBoyOrder = $collection->getFirstItem();
        return $deliveryBoyOrder;
    }
}
