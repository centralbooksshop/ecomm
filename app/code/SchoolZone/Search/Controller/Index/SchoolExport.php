<?php
namespace SchoolZone\Search\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class SchoolExport extends Action
{
    protected FileFactory $fileFactory;
    protected Csv $csvProcessor;
    protected DirectoryList $directoryList;
    protected CollectionFactory $orderCollectionFactory;
    protected OrderRepositoryInterface $orderRepository;
    protected Session $customerSession;
    protected ResourceConnection $resource;
    protected StoreManagerInterface $storeManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        FileFactory $fileFactory,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        
		$request = $this->getRequest();

        $orderId       = trim((string)$request->getParam('orderId'));
        $status        = trim((string)$request->getParam('status'));
        $rollNumber    = trim((string)$request->getParam('rollNumber'));
        $customerEmail = trim((string)$request->getParam('customerEmail'));
        $searchSchool  = trim((string)$request->getParam('searchSchool'));
        $phoneNumber   = trim((string)$request->getParam('phoneNumber'));
        $sdatepicker   = trim((string)$request->getParam('sdatepicker'));
        $edatepicker   = trim((string)$request->getParam('edatepicker'));

        /** ---------------- SCHOOL FALLBACK ---------------- */
        if ($searchSchool === '' && !empty($_SESSION['school_name'])) {
            $searchSchool = $_SESSION['school_name']; // ex: 1357,1627,1849,1850
        }

        // convert CSV string to array
        $schoolIds = [];
        if ($searchSchool !== '') {
            $schoolIds = array_filter(array_map('trim', explode(',', $searchSchool)));
        }
        /** -------------------------------------------------- */

        $data = $this->getOrderData(
            $status,
            $orderId,
            $rollNumber,
            $customerEmail,
            $phoneNumber,
            $schoolIds,
            $sdatepicker,
            $edatepicker
        );

        $fileName = 'exportschoolorders.csv';
        $filePath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . $fileName;

        $this->csvProcessor
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData($filePath, $data);

        return $this->fileFactory->create(
            $fileName,
            ['type' => 'filename', 'value' => $fileName, 'rm' => true],
            DirectoryList::VAR_DIR
        );
    }

    protected function getOrderData(
        string $status,
        string $orderId,
        string $rollNumber,
        string $customerEmail,
        string $phoneNumber,
        array  $schoolIds,
        string $sdatepicker,
        string $edatepicker
    ): array {

        $connection = $this->resource->getConnection();

        $shipmentTable = $this->resource->getTableName('cbo_assign_shippment');
        $driverTable   = $this->resource->getTableName('cboshipping_autodrivers');

        $result = [[
            'ID','Purchase Point','Purchase Date','Courier Name','Tracking Number',
            'Dispatched Date','Bill-to-Name','Ship-to-name','Grand-total(Base)',
            'Grand-total(Purchased)','Student Name','Roll Number','School Name',
            'Status','E-mail','Mobile','Product'
        ]];

        /** ---------------- DATE FILTER (SAFE) ---------------- */
        $fromDate = '2026-01-01 00:00:00';
        $toDate   = '2032-01-01 23:59:59';

        if ($sdatepicker && strtotime($sdatepicker) !== false) {
            $fromDate = date('Y-m-d 00:00:00', strtotime($sdatepicker));
        }

        if ($edatepicker && strtotime($edatepicker) !== false) {
            $toDate = date('Y-m-d 23:59:59', strtotime($edatepicker));
        }
        /** ---------------------------------------------------- */

        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('status', ['nin' => ['order_split']])
            ->addFieldToFilter('created_at', [
                'from' => $fromDate,
                'to'   => $toDate
            ]);

        if (!empty($schoolIds)) {
            $collection->addFieldToFilter('school_id', ['in' => $schoolIds]);
        }

        if ($status !== '') {
            $collection->addFieldToFilter('status', $status);
        }

        if ($orderId !== '') {
            $collection->addFieldToFilter('increment_id', $orderId);
        }

        if ($rollNumber !== '') {
            $collection->addFieldToFilter('roll_no', $rollNumber);
        }

        if ($customerEmail !== '') {
            $collection->addFieldToFilter('customer_email', $customerEmail);
        }

        if ($phoneNumber !== '') {
            $collection->join(
                ['addr' => 'sales_order_address'],
                'main_table.entity_id = addr.parent_id AND addr.address_type = "shipping"',
                ['telephone']
            )->addFieldToFilter('addr.telephone', ['like' => "%$phoneNumber%"]);
        }

        foreach ($collection as $row) {
            $order = $this->orderRepository->get($row->getEntityId());

            $productNames = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $productNames[] = $item->getName();
            }

            $shipment = $connection->fetchRow(
                "SELECT * FROM $shipmentTable WHERE order_id = ?",
                [$row->getEntityId()]
            );

            $trackingTitle = '';
            $trackingNumber = '';
            $dispatchDate = '';

            if ($shipment) {
                $dispatchDate = $shipment['created_at'] ?? '';
                if (!empty($shipment['tracking_title'])) {
                    $trackingTitle  = $shipment['tracking_title'];
                    $trackingNumber = $shipment['tracking_number'];
                } elseif (!empty($shipment['driver_id'])) {
                    $driver = $connection->fetchRow(
                        "SELECT * FROM $driverTable WHERE id = ?",
                        [$shipment['driver_id']]
                    );
                    $trackingTitle  = 'CBO Shipment';
                    $trackingNumber = 'Driver: ' . ($driver['driver_name'] ?? '');
                }
            }

            $result[] = [
                $row->getIncrementId(),
                $row->getStoreName(),
                $row->getCreatedAt(),
                $trackingTitle,
                $trackingNumber,
                $dispatchDate,
                $row->getCustomerFirstname() . ' ' . $row->getCustomerLastname(),
                $row->getCustomerFirstname() . ' ' . $row->getCustomerLastname(),
                $row->getSubtotal(),
                $row->getGrandTotal(),
                $row->getStudentName(),
                $row->getRollNo(),
                $row->getSchoolName(),
                $row->getStatus(),
                $row->getCustomerEmail(),
                $row->getTelephone() ?? '',
                implode(' | ', $productNames)
            ];
        }

        return $result;
    }
}
