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

use Webkul\DeliveryBoy\Model\ResourceModel\OrderTransaction\CollectionFactory as OrderTransationCollF;
use Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory as DeliveryboyOrderCollF;

class TransactionGrid extends \Magento\Backend\Block\Widget\Grid\Extended implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory
     */
    protected $orderTransactionCollF;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderCollF;

    /**
     * @var \Webkul\DeliveryBoy\Model\OrderTransaction\Source\Status
     */
    protected $status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param OrderTransationCollF $orderTransactionCollF
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param DeliveryboyOrderCollF $deliveryboyOrderCollF
     * @param \Webkul\DeliveryBoy\Model\OrderTransaction\Source\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderTransationCollF $orderTransactionCollF,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        DeliveryboyOrderCollF $deliveryboyOrderCollF,
        \Webkul\DeliveryBoy\Model\OrderTransaction\Source\Status $status,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->dateTime = $dateTime;
        $this->_coreRegistry = $coreRegistry;
        $this->helper = $helper;
        $this->orderTransactionCollF = $orderTransactionCollF;
        $this->deliveryboyOrderCollF = $deliveryboyOrderCollF;
        $this->status = $status;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize Grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('deliveryboy_order_transaction');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare colleciton for rendering.
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $orderTransactionCollection = $this->orderTransactionCollF->create();
        $incrementId = $this->getOrder()->getIncrementId();
        $orderTransactionCollection->addFieldToFilter('order_increment_id', $incrementId);
        $orderTransactionCollection->setOrder("created_at", "DESC");
        $this->setCollection($orderTransactionCollection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns.
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __(' Id'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'transaction_id',
            [
                'header' => __('Transaction Id'),
                'index' => 'transaction_id'
            ]
        );
        $orderCurrencyCode = $this->getOrder()->getOptionsWithLabel();
        $this->addColumn(
            'amount',
            [
                'header' => __('Amount'),
                'index' => 'amount',
                'type' => 'currency',
                'currency' => $orderCurrencyCode,
                'sortable' => false,
            ]
        );
        $options = $this->status->getOptionsWithLabel();
        $this->addColumn(
            'is_closed',
            [
                'header' => __('Is Closed'),
                'index' => 'is_closed',
                'type' => 'options',
                'options' => $options
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created Date'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Last Updated'),
                'index' => 'updated_at',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'close',
            [
                'header' => __('Close'),
                'type' => 'action',
                'getter' => 'getId',
                'renderer' => \Webkul\DeliveryBoy\Block\Adminhtml\Template\Grid\Renderer\CloseAction::class,
                'filter' => false,
                'no-link' => true,
                'sortable' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get Grid Url.
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'expressdelivery/ordertransaction/GridData',
            [
                '_current' => true,
            ]
        );
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
     * Get Current Order Id.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Get Current ORder IncrementId.
     *
     * @return int
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * GEt Tab Label.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Deliveryboy Order Transactions');
    }

    /**
     * Get Tab Title.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Deliveryboy Order Transactions');
    }

    /**
     * Return boolean flag for toggling tab.
     *
     * @return bool
     */
    public function canShowTab()
    {
        $order = $this->orderFactory->create()->loadByIncrementId($this->getOrderIncrementId());
        
        $allowedShipping = explode(",", $this->helper->getConfigData("deliveryboy/configuration/allowed_shipping"));
        if (!in_array($order->getShippingMethod(), $allowedShipping)) {
            return false;
        }
        if (!$this->getDeliveryboyOrder()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * Check wheter the tab is hidden or not.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get Current Deliveyrboy Order.
     *
     * @return DeliveryboyOrder
     */
    public function getDeliveryboyOrder()
    {
        $collection = $this->deliveryboyOrderCollF->create()
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
