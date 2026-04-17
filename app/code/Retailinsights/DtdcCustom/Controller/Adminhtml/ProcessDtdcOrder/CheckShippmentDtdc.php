<?php
namespace Retailinsights\DtdcCustom\Controller\Adminhtml\ProcessDtdcOrder;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders\Grid\CollectionFactory as DtdcCollectionFactory;
use Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory as InvoiceResourceCollectionFactory;
use Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory as FedexCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class CheckShippmentDtdc extends \Magento\Backend\App\Action
{
    protected $fedexLabels;
    protected $filter;
    protected $collectionFactory;
    protected $invoiceResourceCountCollection;
    protected $invoiceCountCollection;
    protected $orderRepository;
    protected $shipmentRepository;
    protected $resultJsonFactory;
    protected $_resultPageFactory;
    protected $_orderCollectionFactory;
    protected $searchCriteriaBuilder;

    public function __construct(
        FedexCollectionFactory $fedexLabels,
        Filter $filter,
        DtdcCollectionFactory $collectionFactory,
        \Retailinsights\Orders\Model\PostFactory $invoiceCountCollection,
        InvoiceResourceCollectionFactory $invoiceResourceCountCollection,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->fedexLabels = $fedexLabels;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
        $orderIds = array_unique(array_map('trim', (array)$orderIds));

        $result = [];
        foreach ($orderIds as $incrementId) {
            $result[$incrementId] = $this->getOrderStatus($incrementId);
        }

        return $this->resultJsonFactory->create()->setData($result);
    }

    public function getOrderStatus($orderIncrementId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementId);

        $status = $orderInfo->getStatus();
        $orderId = $orderInfo->getId();

        if (!$orderId) {
            return 'no order id';
        }

        if (in_array($status, ['dispatched_to_courier', 'order_delivered'])) {
            return $status;
        }

        if (in_array($status, ['complete', 'pending', 'processing'])) {
            $order = $this->orderRepository->get($orderId);
            $shipment = $order->getShipmentsCollection();

            if (empty($shipment->getData())) {
                return 'no';
            }

            if ($this->checkPayslipGenerated($orderInfo) === 'no') {
                return 'no_pay_slip_generated';
            }

            if ($this->checkFedexLabel($orderInfo) === 'yes') {
                return 'no_tracking_info';
            }

            if ($this->checkDtdcLabel($orderInfo) === 'no') {
                return 'no_tracking_info';
            }

            return 'yes';
        }

        return 'no';
    }

    public function checkPayslipGenerated($order)
    {
        $orderId = $order->getId();
        $invoiceId = '';

        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceId = $invoice->getId();
        }

        if (!$invoiceId) {
            return 'no';
        }

        $collection = $this->invoiceResourceCountCollection->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('invoice_id', $invoiceId);

        foreach ($collection as $value) {
            if (trim($value['invoice_id']) === trim($invoiceId)) {
                return 'yes';
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

        return !empty($collection->getFirstItem()->getData('order_id')) ? 'yes' : 'no';
    }

   /*  public function checkDtdcLabel($order)
    {
        $orderId = $order->getId();
        $collection = $this->collectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('order_id', $orderId);
    
        return !empty($collection->getFirstItem()->getData('orderid')) ? 'yes' : 'no';
    } */

	public function checkDtdcLabel($order)
	{
		$orderId = $order->getId();

		/** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
		$collection = $this->_orderCollectionFactory->create()
			->addFieldToSelect(['entity_id', 'increment_id', 'shipsy_reference_numbers'])
			->addFieldToFilter('entity_id', $orderId);

		$orderData = $collection->getFirstItem();

		// Debug line (optional — use logging instead of echo in production)
		 //echo '<pre>'; print_r($orderData->getData()); die;

		if (!empty($orderData->getData('shipsy_reference_numbers'))) {
			return 'yes';  // Label exists for DTDC
		}

		return 'no'; // No DTDC label yet
	}
}
