<?php

namespace Retailinsights\Orderexport\Controller\Adminhtml\Exportorder;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Post
 * @package Retailinsights\Orderexport\Controller\Adminhtml\Exportorder\Post
 */
class Post extends \Magento\Framework\App\Action\Action
{   protected $postCollection;
     /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;
    protected $transactions;
    /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
    protected $_rawResultFactory;
    protected $order;
    protected $storeManager;
    protected $orderCollectionFactory;
    protected $timezone;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Retailinsights\Orderexport\Model\ResourceModel\Post\CollectionFactory $postCollection,
        \SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolsCollection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
         CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions,
        Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderRepository $order,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
       \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->postCollection = $postCollection;
        $this->schoolsCollection = $schoolsCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->transactions = $transactions;
        parent::__construct($context);
        $this->_rawResultFactory = $context->getResultFactory();
        $this->order = $order;
         $this->timezone = $timezone;
        $this->storeManager=$storeManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }
      public function execute() {
        $resultRaw = $this->_rawResultFactory->create(ResultFactory::TYPE_RAW);
        $response = '-failure-1';
        $params = $this->getRequest()->getParams();
        $fromDate_val = $params['from_date'];
        $toDate_val = $params['to_date'];
        //$productId = isset($params['product']) ? (int)$params['product'] : 0;
        if(isset($fromDate_val))
        {
         try {

             if (strtotime($fromDate_val) > strtotime($toDate_val)) {
                //redirect if Start date greater than End date
                $messageManager = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
                $messageManager->addError(__("End date should be greater than Start date"));
                $this->_redirect('orderexport/exportorder');
                return;
            }
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
                $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                $finalmediaPath = $mediaPath.'export.csv';
                $fp = fopen("$finalmediaPath","w+");
                $i=0;

                if($i==0){
                $data = array();
                $data[] ="Magento Invoice No";
                $data[] = "Magento Order No";
                $data[] = "Split order Parent ID";
                $data[] = "Magento Invoice Date";
                $data[] = "Magento Export Date";
                $data[] = "Sell-to Customer No";
                $data[] = "Bill-to Customer No";
                $data[] = "No.";
                $data[] = "ISBN";
                //$data[] = "Product Purchased";
                $data[] = "Title";
                $data[] = "SKU";
                $data[] = "Quantity";
                $data[] = "MRP";
                $data[] = "Discount %";
                $data[] = "Discount Amount";
                $data[] = "Line Amount";
                $data[] = "GST Group Code";
                $data[] = "CGST Amount";
                $data[] = "SGST Amount";
                $data[] = "IGST Amount";
                $data[] = "Taxable";
                $data[] = "Order Amount";
                $data[] = "Shipping Charges";
                $data[] = "Transaction ID";
                $data[] = "CreditMemo Id";
                $data[] = "Customer State";
                $data[] = "Customer Pincode";
                $data[] = "Location Code";
                $data[] = "Refund Orders";
                $data[] = "Will be Given";
                $data[] = "School Given";
                $data[] = "Purchase Point";
                $data[] = "Order Status";
                
                fputcsv($fp, $data); 
            }
            $i++;

        // Get order collection
        $status = array('order_delivered');
        $status1 = array('closed');

        $fromDate = date('Y-m-d 00:00:00', strtotime($fromDate_val));
        $toDate = date('Y-m-d 23:59:59', strtotime($toDate_val));

        $start = microtime(true);
        

        $orderCollection = $this->orderCollectionFactory
                        ->create()->addAttributeToSelect("*")
                        ->addFieldToFilter('updated_at', array('gteq' => $fromDate))
                        ->addFieldToFilter('updated_at', array('lteq' => $toDate))
                        ->addFieldToFilter('status', array(
                            array('in' => $status),
                            array('in' => $status1)

                        ));

      //$orderCollection->addFieldToFilter('created_at', ['lteq' => $edate])->addFieldToFilter('created_at', ['gteq' => $sdate]);

        if ($orderCollection && count($orderCollection) > 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
            foreach ($orderCollection AS $order) {
                $schoolCode = $order->getSchoolCode();
                $creditMemos = $order->getCreditmemosCollection();
                $order_id = $order->getId();
                $incrementID = "OrderNo".$order->getIncrementId();
                $locationCode = $order->getLocationCode();
                $parentsplitorderID = $order->getParentSplitOrder();
                $willBeGiven = '';
                $schoolGiven = '';
                //$order = $this->orderFactory->create()->load($order_id);
                //$orderproduct = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
                //$orderproduct = $this->order->get($order_id);
                $orderproduct = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($order_id);
                $storeId = $order->getStoreId();
                $orderstatus = $order->getStatusLabel();

                $purchasePoint = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\Magento\Store\Model\StoreManagerInterface::class)
                        ->getStore($storeId)
                        ->getName();
 

                $orderItems = $orderproduct->getAllItems();
            
                $Grandtotal=$order->getGrandTotal();
                $region = $orderproduct->getShippingAddress()->getRegion();
                $shipmentLocation = $order->getShipmentLocation();
                $postcode = $orderproduct->getShippingAddress()->getPostcode();
                
                //////////
                $invoiceCollection = $orderproduct->getInvoiceCollection();
                $invoiceIncrementID = '';
                $invoiceDate = '';
                foreach($invoiceCollection as $invoice){
                    $invoiceIncrementID = "InvoiceNo".$invoice->getIncrementId();// invoice increment id
                    $invoiceDate = $invoice->getCreatedAt();
                    // same way get other details of invoice
                }
        
        if($orderproduct->getSchoolName()) { 
            $school_code = '';
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $select = $connection->select('school_code')->from( ['schools_registered'])->where('school_name_text=?',$orderproduct->getSchoolName());
            $data = $connection->fetchAll($select);
            foreach ($data as $k => $v) {$school_code=$v['school_code'];}
            
            
        } else {
             $school_code="WALK-IN ONLINE";
         }  
     $shippingamount=$orderproduct->getShippingAmount();
       
        $paymentmethod=$orderproduct->getPayment()->getAdditionalInformation('method_title');
        $billtoNum="";
        if($paymentmethod) {
            $paymentvalue="";
            $payment_path="";
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $select = $connection->select('path')->from( ['core_config_data'])->where('value=?',$paymentmethod);
            $data = $connection->fetchAll($select);
            foreach ($data as $k => $v) {$payment_path=$v['path'];}
            if($payment_path!=''){
                $path_expload=explode("/",$payment_path);
                $path= $path_expload[0]."/".$path_expload[1]."/payment_code";

                $select = $connection->select('value')->from( ['core_config_data'])->where('path=?',$path);
                $data = $connection->fetchAll($select);
                foreach ($data as $k => $v) {$paymentvalue=$v['value'];}
            }

            $billtoNum=$paymentvalue;
        }


	foreach ($orderItems as $item){         
            $creditMemoId='';
            foreach ($creditMemos as $creditMemo) { //go through all the credit memos for the current order.       
                $creditMemoId=$creditMemo->getIncrementId();
            }

            $transactions = $this->transactions->create()->addOrderIdFilter($order_id);
            
            $transactionsIds ='';
            foreach ($transactions->getItems() as $key => $value) {
                $transactionsIds .=$value->getTxnId().',';
            }

            /*$locationCode='';
            if($schoolCode!= ''){
                $schoolCollection = $this->schoolsCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('school_code', $schoolCode);
                $schoolData = $schoolCollection->getLastItem(); 
                //echo '<pre>' ;print_r($schoolData);
                
                if(!empty($schoolData)){
                    $locationCode = $schoolData['location_code'];
                }
            }*/

            
            if($item->getProductType()=="bundle") {
               $bundle_item_id = $item->getProductId();
               //$bundle_product_purchase = $item->getName();
               $product_given_collection = $this->postCollection->create()->addFieldToFilter('parent_product_id', $bundle_item_id);
             } else {
                $product_given_collection = array();
                //$bundle_product_purchase = '';
             }
            
            if($item->getProductType()!="bundle") {
                $productid = $item->getProductId();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                //$product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->getById($productid);
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                $isbnnum=$product->getIsbn();
                $itemnum=$product->getNavisionItemNumber();
		$taxclassid=$product->getTaxClassId();
                $totalwithoutDiscount= $item->getRowTotal();  //$item->getQtyOrdered()*$item->getPrice();
                
                //$product_given_collection->addFieldToFilter('product_id', $productid);
                //echo $product_given_collection->getSelect(); 
                //echo '<pre>'; print_r($productid);
                $optinal_given_value = '';
                    if(!empty($product_given_collection)) {
                      foreach($product_given_collection as $given_item_value){
                        $optinal_product_id = $given_item_value->getProductId();
                        if($optinal_product_id == $productid ) {
                            $optinal_update_date = $given_item_value->getUpdatedAt(); 
                          $final_optinal_update_date = $this->timezone->date(new \DateTime($optinal_update_date))->format('Y/m/d H:i:s');
                          if($optinal_update_date != '') {
                                $optinal_final_date = strtotime($final_optinal_update_date);
                                $order_date = strtotime($invoiceDate);
                                if ($optinal_final_date > $order_date) {
                                   //echo 'optinal_update_date greater than';
                                    $optinal_given_value = $given_item_value->getCustomField();
                                } 
                            }
                         }
                      }
                  }

                // if($region=="Telangana"){
                //     $igst=$item->getTaxAmount();
                //     $cgst=0;
                //     $sgst=0;                        
                // } else {
                //     $igst=0;
                //     $cgst=$item->getTaxAmount()/2;
                //     $sgst=$item->getTaxAmount()/2;
                // }

                  if(($region == "Telangana" && $shipmentLocation == "Telangana") || ($region == "Maharashtra" && $shipmentLocation == "Maharashtra")){
                    $igst=0;
                    $cgst=$item->getTaxAmount()/2;
                    $sgst=$item->getTaxAmount()/2;                                              
                } else if(($region != "Telangana" && $shipmentLocation == "Telangana") ||  ($region != "Maharashtra" && $shipmentLocation == "Maharashtra")){
                    $igst=$item->getTaxAmount();
                    $cgst=0;
                    $sgst=0;     
                }else{
                                        $igst=0;
                                        $cgst=$item->getTaxAmount()/2;
                                        $sgst=$item->getTaxAmount()/2;
                }

                $tax_class_name="";
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $select = $connection->select('class_name')->from( ['tax_class'])->where('class_id=?',$taxclassid);
                $data1 = $connection->fetchAll($select);
                foreach ($data1 as $k => $t) {$tax_class_name=$t['class_name'];}

                $data = array(); 
                $data[] = $invoiceIncrementID;
                $data[] = $incrementID;
                $data[] = $parentsplitorderID;
                $data[] = $invoiceDate; 
                $data[] = date("Y-m-d");
                $data[] = $school_code;
                $data[] = $billtoNum;
                $data[] = $itemnum;
                $data[] = $isbnnum;
                //$data[] = $bundle_product_purchase;
                $data[] = $item->getName();
                $data[] = $item->getSku();
                $data[] = $item->getQtyOrdered();
                $data[] = $item->getOriginalPrice();
                $data[] = $item->getDiscountPercent();
                $data[] = $item->getDiscountAmount();
                $data[] = $item->getPrice()-$item->getDiscountAmount();
                $data[] = $tax_class_name;
                $data[] = $cgst;
                $data[] = $sgst;
                $data[] = $igst;
                $data[] = $totalwithoutDiscount; //-$item->getDiscountAmount();
                $data[] = $Grandtotal;
                $data[] = $shippingamount;
                $data[] = $transactionsIds;
                $data[] = $creditMemoId;
                $data[] = $region;
                $data[] = $postcode;
                $data[] = $locationCode;
    
                if($creditMemoId!=''){
                    $data[] = 'Refunded';
                }else{
                    $data[] = '';
                }

                if($optinal_given_value == 1){
                    $data[] = 'Will Be given';                  
                }else{
                    $data[] = '';                   
                }
                if($optinal_given_value == 2){
                    $data[] = 'School Given';                   
                }else{
                    $data[] = '';                   
                }

                $data[] = $purchasePoint;
                $data[] = $orderstatus;
                fputcsv($fp, $data); 
            }   
	}
//	die;
        
    }
    //echo $time_elapsed_secs = microtime(true) - $start; die;
   
}
            
                $filename = 'export.csv';
                fseek($fp, 0);
                header('Content-Encoding: UTF-8');
                header('Content-type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachement; filename="' . $filename . '"');
                fpassthru($fp);
             
             
                /* if($i > 0) {
                  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                     //$response = $this->storeManager->getStore()->getBaseUrl().'var/export/export.csv';
                     $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                     $baseurl=$storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                     $response =$baseurl."pub/media/export.csv";
                 } else {
                    $response = 'failure-2';
                 }
                 fclose($fp); */
               // $response = array('status'=>'success','message'=>'Product added successfully');
            }
            catch (Exception $e) {
            $messageManager = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
            $messageManager->addError(__("End date should be greater than Start date"));
            $this->_redirect('orderexport/exportorder');
            return;
          }
        }

        
    }
}
