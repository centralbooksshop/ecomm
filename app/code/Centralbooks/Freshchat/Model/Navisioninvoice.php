<?php /** @noinspection ALL */

namespace Centralbooks\Freshchat\Model;

use Magento\Framework\Model\Context;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;


/**
 *
 */
class Navisioninvoice
{
    private $invoice;

    protected $order;
    protected $storeManager;
    
    protected  $_resource;
	protected $logger;
    
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\OrderRepository $order,
        \SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolsCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions,
        \Centralbooks\Freshchat\Helper\Data $helperData,
		\Psr\Log\LoggerInterface $logger,
         \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
         \Magento\Framework\App\ResourceConnection $resource
    ) {

        $this->invoiceRepository = $invoiceRepository;
        $this->invoice = $invoice;
        $this->order = $order;
        $this->schoolsCollection = $schoolsCollection;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->storeManager=$storeManager;
        $this->transactions = $transactions;
        $this->helper = $helperData;
		$this->logger = $logger;
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->_resource = $resource;
    }

    public function invoicesynch() {
        $this->logger->info('here excuting');

        $time = time();
        $to = date('Y-m-d H:i:s', $time);
        $lastTime = $time - 86400; // 60*60*24
        $from = date('Y-m-d H:i:s', $lastTime);

        $invoiceCollection = $this->_invoiceCollectionFactory->create()
                ->addAttributeToSelect('increment_id')
                ->addAttributeToSelect('entity_id')
                ->addFieldToFilter('navision_sync', array('null' => true))
                ->addFieldToFilter('created_at', array('from' => $from, 'to' => $to));

        $countor = count($invoiceCollection);
        $this->logger->info('if found update navision synch===' . print_r($countor, true)); // Array Log
        foreach ($invoiceCollection as $invoice) {
              $this->logger->info('if found update invoice id===' . print_r($invoice->getId(), true)); // Array Log
            $this->docall($invoice->getId(), false);
        }
    }

    public function docall($invoiceId) {
        
        $this->logger->info('here excuting');
        //$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $this->_resource->getConnection();//$resource->getConnection();
        
        $invoice = $this->invoice->load($invoiceId);
        $this->logger->info('Array Log invoice_id' . print_r($invoice->getId(), true)); // Array Log
        
        $order = $invoice->getOrder();
        $order_id = $order->getId();
        $this->logger->info('Array Log order id' . print_r($order_id, true)); // Array Log
        
        $orderproduct = $this->order->get($order_id);
        
        $schoolCode = $orderproduct->getSchoolCode();
        $this->logger->info('Array Log school code' . print_r($schoolCode, true)); // Array Log
        
        $orderincrementID = $orderproduct->getIncrementId();
        $invoiceIncrementID = $invoice->getIncrementId(); // invoice increment id
        
        $region = $orderproduct->getShippingAddress()->getRegion();
        $this->logger->info('Array Log region' . print_r($region, true)); // Array Log
        
//        $orderItems = $orderproduct->getAllItems();
        $items = $invoice->getAllItems();

        $this->logger->info('Array Log itemscount' . print_r(count($items), true)); // Array Log

        $lineitems = array();
        foreach ($items as $item) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $this->logger->info('Array Log item id' . print_r($item->getId(), true)); // Array Log
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
            $isbnnum = $product->getIsbn();
            $itemnum = $product->getNavisionItemNumber();
            $taxclassid = $product->getTaxClassId();
            $this->logger->info('Array Log taxclassid' . print_r($taxclassid, true)); // Array Log
            $tax_class_name = "";
            if ($taxclassid != 0) {
                $select = $connection->select('class_name')->from(['tax_class'])->where('class_id=?', $taxclassid);
                $data1 = $connection->fetchAll($select);
                foreach ($data1 as $k => $t) {
                    $tax_class_name = $t['class_name'];
                }
            } else {
                $tax_class_name = "EXEMPTED";
            }
            $this->logger->info('Array Log taxamount' . print_r($item->getTaxAmount(), true)); // Array Log
            $this->logger->info('Array Log taxamount percent' . print_r($item->getOrderItem()->getTaxPercent(), true)); // Array Log
            $this->logger->info('Array Log discount percent' . print_r($item->getOrderItem()->getDiscountPercent(), true)); // Array Log
            
            if ($region == "Telangana") {
                $igst = $item->getTaxAmount();
                $cgst = 0;
                $sgst = 0;
            } else {
                $igst = 0;
                $cgst = $item->getTaxAmount() / 2;
                $sgst = $item->getTaxAmount() / 2;
            }
            
            $this->logger->info('Array Log igst == ' . print_r($igst, true)); // Array Log
            $this->logger->info('Array Log cgst ==' . print_r($cgst, true)); // Array Log
            $this->logger->info('Array Log sgst ==' . print_r($sgst, true)); // Array Log
            
            $lineitems[] = array("itemid" => $item->getSku(),
                "qty" => $item->getQty(),
                "mrp" => $item->getPrice(),
                "linediscpct" => $item->getOrderItem()->getDiscountPercent(),
                "linedicamt" => $item->getOrderItem()->getDiscountAmount(),
                "lineamt" => $item->getRowTotal(),
                "gstcode" => $tax_class_name,
                "gstpct" => $item->getOrderItem()->getTaxPercent(),
                "gstamt" => $item->getTaxAmount(),
                "hsn" => $product->getHsn(),
                "isbn" => $isbnnum,
                "Class" => $product->getClassSchool(),
                "sgst" => $sgst,
                "cgst" => $cgst,
                "igst" => $igst,
            );
        }
        $this->logger->info('Array Log line items' . print_r($lineitems, true)); // Array Log
//        $locationCode = '';
//        if ($schoolCode != '') {
//            $schoolCollection = $this->schoolsCollection->create()
//                    ->addFieldToSelect('*')
//                    ->addFieldToFilter('school_code', $schoolCode);
//            $schoolData = $schoolCollection->getData();
//            $this->logger->info('Array Log school data' . print_r($schoolData, true)); // Array Log
//            $locationCode = $schoolData[0]['location_code'];
//        }
        if ($orderproduct->getSchoolName()) {

            $select = $connection->select('school_code')->from(['schools_registered'])->where('school_name_text=?', $orderproduct->getSchoolName());
            $data = $connection->fetchAll($select);
            foreach ($data as $k => $v) {
                $school_code = $v['school_code'];
            }
        } else {
            $school_code = "WALK-IN ONLINE";
        }
        $paymentmethod = $orderproduct->getPayment()->getAdditionalInformation('method_title');
        $billtoNum = "";
        if ($paymentmethod) {
            $paymentvalue = "";
            $payment_path = "";
            $select = $connection->select('path')->from(['core_config_data'])->where('value=?', $paymentmethod);
            $data = $connection->fetchAll($select);
            foreach ($data as $k => $v) {
                $payment_path = $v['path'];
            }
            if ($payment_path != '') {
                $path_expload = explode("/", $payment_path);
                $this->logger->info('path->' . $payment_path);

                $path = $path_expload[0] . "/" . $path_expload[1] . "/payment_code";

                $select = $connection->select('value')->from(['core_config_data'])->where('path=?', $path);
                $data = $connection->fetchAll($select);
                foreach ($data as $k => $v) {
                    $paymentvalue = $v['value'];
                }
            }

            $billtoNum = $paymentvalue;
        }
        $invoicedata = array(
            "docno" => $invoice->getIncrementId(),
            "externaldocno" => $orderproduct->getIncrementId(),
            "selltocustno" => $school_code,
            "billtocustno" => "WALK-IN ABIDS",
            "paymentmethod" => "cash", //$billtoNum,
            "postingdate" => date("Y-m-d"),
            "shippingcharges" => $invoice->getShippingAmount(),
            "items" => $lineitems,
        );

        $invoicefinal = json_encode($invoicedata);
        $this->logger->info('Array Log line items' . print_r($invoicefinal, true)); // Array Log
        try {
            $accesstoken = $this->helper->GenerateToken();
            if ($accesstoken != "error") {
                $this->helper->getNavisionlogging("itemsynch started token got");
//                echo $accesstoken;
                $curl = curl_init();


                curl_setopt_array($curl, array(
                    CURLOPT_URL => $this->helper->getApiurl() . 'CBSUATAPI/api/b2csi_add', //$this->helper->getApiurl() . 'CBSUATAPI/api/MasterItems',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $invoicefinal,
                    CURLOPT_HTTPHEADER => array(
                        'Authorization:' . $accesstoken,
                        'username:' . $this->helper->getUsername(),
                        'password:' . $this->helper->getPassword(),
                        'version: 1',
                        'Content-Type: application/json'
                    ),
                ));
                $response = curl_exec($curl);
//                        print_r($response);
                curl_close($curl);

                $rep = json_decode($response);
                if ($rep->status == "success") {
                    $this->helper->getNavisionlogging("itemsynch success reposnce" . $response);

                    $invoice->setNavisionSynch("synched");
                    $invoice->save();
                    $tableName = $resource->getTableName('sales_order');

                    $sql_update = "update  " . $tableName . " set  navision_sync='synced' where entity_id=" . $order_id;
                    $result = $connection->query($sql_update);
                    $this->logger->info('orderId Sent' . $sql_update);
//                            $this->createNavisionProduct($rep);
//                            print_r($rep);
                } else {
                    $this->helper->getNavisionlogging($rep);
                }
            } else {
                $this->helper->getNavisionlogging($accesstoken);
            }
        } catch (Exception $ex) {
            
        }
    }

}