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

class Export extends Action
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

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToSelect([
            'entity_id',
            'increment_id',
            'created_at',
            'subtotal',
            'grand_total',
            'status',
            'customer_email',
            'customer_firstname',
            'customer_lastname',
            'student_name',
            'roll_no',
            'school_name',
            'store_id'
        ]);
           $collection->addFieldToFilter('main_table.status', ['nin' => ['order_split']]);
           $collection->addFieldToFilter('main_table.created_at', [
                'from' => $fromDate,
                'to'   => $toDate
            ]);
         /*   $collection->getSelect()->joinLeft(
                ['cbo' => $shipmentTable],
                'main_table.entity_id = cbo.order_id',
                ['tracking_title','tracking_number','cbo_created_at'=>'created_at','driver_id']
            )->joinLeft(
                ['driver' => $driverTable],
                'cbo.driver_id = driver.id',
                ['driver_name']
            )->joinLeft(
                ['delivery' => $connection->getTableName('deliveryboy_deliveryboy')],
                'cbo.deliveryboy_id = delivery.id',
                ['deliveryboy_name' => 'delivery.name']
                ); */
                $shipmentTable = $connection->getTableName('cbo_assign_shippment');
    $latestShipmentSubQuery = "
        SELECT *
        FROM {$shipmentTable} s1
        WHERE s1.id = (
            SELECT MAX(s2.id)
            FROM {$shipmentTable} s2
            WHERE s2.order_id = s1.order_id
        )
    ";
    $collection->getSelect()->joinLeft(
        ['cbo' => new \Zend_Db_Expr("({$latestShipmentSubQuery})")],
        'main_table.entity_id = cbo.order_id',
        [
            'tracking_title'  => 'cbo.tracking_title',
            'tracking_number' => 'cbo.tracking_number',
            'cbo_created_at'  => 'cbo.created_at',
            'driver_id'       => 'cbo.driver_id',
            'deliveryboy_id'  => 'cbo.deliveryboy_id'
        ]
    );
    $collection->getSelect()->joinLeft(
        ['driver' => $connection->getTableName('cboshipping_autodrivers')],
        'cbo.driver_id = driver.id',
        ['driver_name' => 'driver.driver_name']
    );

    $collection->getSelect()->joinLeft(
        ['delivery' => $connection->getTableName('deliveryboy_deliveryboy')],
        'cbo.deliveryboy_id = delivery.id',
        ['deliveryboy_name' => 'delivery.name']
    );
        if (!empty($schoolIds)) {
            $collection->addFieldToFilter('main_table.school_id', ['in' => $schoolIds]);
        }

        if ($status !== '') {
            $collection->addFieldToFilter('main_table.status', $status);
        }

        if ($orderId !== '') {
            $collection->addFieldToFilter('main_table.increment_id', $orderId);
        }

        if ($rollNumber !== '') {
            $collection->addFieldToFilter('main_table.roll_no', $rollNumber);
        }

        if ($customerEmail !== '') {
            $collection->addFieldToFilter('main_table.customer_email', $customerEmail);
        }

        if ($phoneNumber !== '') {
            $collection->join(
                ['addr' => 'sales_order_address'],
                'main_table.entity_id = addr.parent_id AND addr.address_type = "shipping"',
                ['telephone']
            )->addFieldToFilter('addr.telephone', ['like' => "%$phoneNumber%"]);
        }
        $collection->getSelect()->group('main_table.entity_id');
        $orderIds = $collection->getAllIds();
        $select = $connection->select()
            ->from(
                $connection->getTableName('sales_order_item'),
                ['order_id', 'name']
            )
            ->where('order_id IN (?)', $orderIds)
            ->where('parent_item_id IS NULL');

    $rows = $connection->fetchAll($select);
    $productMap = [];
    foreach ($rows as $row) {
        $productMap[$row['order_id']][] = $row['name'];
    }
    foreach ($collection as $row) {
    $trackingTitle  = $row->getTrackingTitle();
    $trackingNumber = $row->getTrackingNumber();
    $dispatchDate   = $row->getCboCreatedAt();
    if (!empty($dispatchDate)) {
        $trackingTitle =$trackingTitle ?: 'CBO Shipment';
    }
    if (!empty($row->getDriverId())){
         $trackingNumber = 'Driver: '.$row['driver_name'];
        } elseif (!empty($row['deliveryboy_id'])){
            $trackingNumber =' Deliveryboy Name:'.$row['deliveryboy_name'];  }
            elseif (!empty($row['tracking_number'])){
            $trackingNumber =  $row['tracking_number']; 
            }

    $result[] = [
        $row->getIncrementId(),
        $this->storeManager->getStore($row->getStoreId())->getName(),
        $row->getCreatedAt(),
        $trackingTitle,
        $trackingNumber,
        $dispatchDate,
        $row->getCustomerFirstname().' '.$row->getCustomerLastname(),
        $row->getCustomerFirstname().' '.$row->getCustomerLastname(),
        $row->getSubtotal(),
        $row->getGrandTotal(),
        $row->getStudentName(),
        $row->getRollNo(),
        $row->getSchoolName(),
        $row->getStatus(),
        $row->getCustomerEmail(),
        '',
        implode(' | ', $productMap[$row->getEntityId()] ?? [])
    ];
}
        return $result;
    }
}
