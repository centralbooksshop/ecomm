<?php


namespace Centralbooks\Freshchat\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\App\ResourceConnection;

class Navisionproduct {

    protected $logger;
    protected $jsonResultFactory;
    protected $request;
    private $productAction;
    private $storeManager;
    protected $orderCollectionFactory;

    /** @var \Magento\Sales\Api\Data\OrderInterfaceFactory $order **/

protected $orderFactory;
    
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
    ) {

        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->_customerFactory = $customerFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
    }

   

    /**
     * {@inheritdoc}
     */
    public function getCustomerorder($mobile) {
        $this->logger->info('Simple Text Log');
        // print(http_response_code());
        $connection = $this->resourceConnection->getConnection();
        $customerdata = $this->_customerFactory->create()->getCollection()
                ->addFieldToSelect("entity_id")
                ->addFieldToSelect("email")
                ->addFieldToSelect("mobile_number");
              //->addAttributeToFilter("mobile_number", array("like" => '%' . $mobile . '%')); //-load();
        if(filter_var($mobile, FILTER_VALIDATE_EMAIL)){
         // echo("$mobile is a valid email address");
           $customerdata->addAttributeToFilter("email", array("eq" => $mobile)); //-load();
        } else {
          // echo("$mobile is not a valid email address");
          $customerdata->addAttributeToFilter("mobile_number", array("like" => '%' . $mobile . '%')); //-load();
        }
                
        $orderobject = array();
        if (count($customerdata) > 0) {

            $collectionData = $customerdata->toArray();

            foreach ($customerdata as $customer) {
                $customerId = $customer->getId();
            }

            $orders = $this->orderCollectionFactory->create();
            $orders->addFieldToSelect('*');
            $orders->addFieldToFilter('customer_id', $customerId)->setOrder('created_at', 'desc');

            $orders->setPageSize(5)->setCurPage(1);
//            echo "ordercount" . $orders->count();
             $trackinginformation = array();
            if ($orders->count() > 0) {
                $orderdata=array();
//                $alldata= array();
//               $trackinginformation=array();
               foreach ($orders as $order) {
//                   echo $order->getId();
                    $orderdata['id'] = $order->getData('entity_id');
                    $orderdata['state'] = $order->getData('state');
                    $orderdata['status'] = $order->getData('status');
                    $orderdata['increment_id'] = $order->getData('increment_id');
                    $tracksCollection = $order->getTracksCollection();
                    if ($order->getTracksCollection() && count($tracksCollection) > 0 && $tracksCollection->getItems()) {
                        foreach ($tracksCollection->getItems() as $track) {
                            if ($track->getTrackNumber()) {
                                $trackinginformation['tracknumber'] = $track->getTrackNumber();
                                $trackinginformation['tracktitle'] = $track->getTitle();
                                $trackinginformation['created_at'] = $track->getCreatedAt();
                            }
                        }
                        $orderdata["tracking_information"] = $trackinginformation;
                    } else {
                        $orderdata['tracking_information'] =  'notrack';
                    }
                   $alldata[]=$orderdata;
                }
                
                $response[] = ['Status' => "success", 'message' => $alldata];

                /**
                 * one order get and send 
                  $lastestOrder = $orders->getFirstItem();

                  $tracksCollection = $lastestOrder->getTracksCollection();
                  print_r($tracksCollection->getData());
                  $orderobject['id'] = $lastestOrder->getData('entity_id');
                  $orderobject['state'] = $lastestOrder->getData('state');
                  $orderobject['status'] = $lastestOrder->getData('status');
                  $orderobject['increment_id'] = $lastestOrder->getData('increment_id');
                  $trackinginformation = array();
                  if(count($tracksCollection) > 0 && $lastestOrder->hasShipments()){
                    foreach ($tracksCollection->getItems() as $track) {
                      $trackinginformation['tracknumber'] = $track->getTrackNumber();
                      $trackinginformation['tracktitle'] = $track->getTitle();
                      $trackinginformation['created_at'] = $track->getCreatedAt();
                    }
                    $orderobject["tracking_information"]=$trackinginformation;
                  }
                  $response[] = ['Status' => "success", 'message' => $orderobject];
                  end of one order
                **/
            }elseif(http_response_code()!= 200){
                $response[] = ['Status' => "false", 'message' => "error found insystem please check exception or debuglogs"];
            } else {
                $lastestOrder = false;
                $response[] = ['Status' => "false", 'message' => "no Orders found"];
            }
        } else {
            $response[] = ['Status' => "false", 'message' => "no cusotmer found"];
        }
        header('Content-type: application/json');
        echo $json = json_encode($response, JSON_UNESCAPED_SLASHES);
        die();
       //return $returnArray;
    }

    /**
     * {@inheritdoc}
     */
    public function Creatermabot($id) {
       $this->logger->info('Simple Text Log'); // Simple Text Log
        
        $order = $this->orderFactory->create()->loadByIncrementId($id);
        $orderid = $order->getId();
        if($orderid){
            $baseurl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $str ='<a href=' . $baseurl . 'default/prrma/returns/create/order_id/'. $orderid . '>Please Click Here and submit RMA</a>';
            $response[] = ['Status' => "true", 'message' => $str];
        }else{
            $response[] = ['Status' => "fasle", 'message' => "Order not found"]; 
        }
        header('Content-type: application/json');
        echo $json = json_encode($response, JSON_UNESCAPED_SLASHES);
        die();
    }

    public function getRMAinformation($id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Plumrocket\RMA\Model\Returns')->load($id);
//         print_r($customerObj->getData());
//        print_r($customerObj->getTracks());
//        echo count($customerObj->getTracks());
//        foreach ($customerObj->getTracks() as $track){
//            print_r($track->getData());
//        }
        
        
        
        $rmaobject = array();
        if ($customerObj) {
            $rmaobject['id'] = $customerObj->getEntityId();
            $rmaobject['rmaincrementid'] = $customerObj->getIncrementId();
            $rmaobject['status'] = $customerObj->getStatus();
            $rmaobject['status_label'] = $customerObj->getStatusLabel();
            $rmaobject['created_at'] = $customerObj->getCreatedAt();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($customerObj->getOrderId());
//            echo $order->getId();
//            echo $order->getRealOrderId();
            $rmaobject['orderid'] = $order->getRealOrderId();
             
            $rmatrackinginformation = array();
                if(count($customerObj->getTracks()) > 0 ){
//                    echo "dflkhkfdjgf";
                    foreach ($customerObj->getTracks() as $track) {
//                         print_r($track->getData());
                        $rmatrackinginformation['tracknumber'] = $track->getTrackNumber();
                        $rmatrackinginformation['tracktitle'] = $track->getCarrierCode();
                        $rmatrackinginformation['created_at'] = $track->getCreatedAt();
                    }
                    $rmaobject["tracking_information"]=$rmatrackinginformation;
                }
            $response = ['Status' => "true", 'message' => $rmaobject];
        } else {
            $response = ['Status' => "true", 'message' => "No RMA found"];
        }
        //echo $customerObj->getStatus();
//        echo "dfgd===" . $customerObj->getStatusLabel();
//        print_r($id);
//        die("hehrerhe");
        header('Content-type: application/json');
        echo $json = json_encode($response, JSON_UNESCAPED_SLASHES);
        die();
    }
    
    public function getIteminformation($id) {
        $order = $this->orderFactory->create()->loadByIncrementId($id);
        $orderid = $order->getId();
//        echo "order id" . $orderid."===".$id;
        $items = $order->getAllVisibleItems(); //getAllItems();
        $itemobject = array();
        $itemresponce = array();
        $allitems = array();
        if ($items) {
            $itemobject['ordernumber'] = $id;
            $itemobject['state'] = $order->getData('state');
             $itemobject['status'] = $order->getData('status');
             $itemobject['createddate'] = $order->getData('created_at');
            $tracksCollection = $order->getTracksCollection();
            if ($order->getTracksCollection() && count($tracksCollection) > 0 && $tracksCollection->getItems()) {
                foreach ($tracksCollection->getItems() as $track) {
                    if ($track->getTrackNumber()) {
                        $trackinginformation['tracknumber'] = $track->getTrackNumber();
                        $trackinginformation['tracktitle'] = $track->getTitle();
                        $trackinginformation['created_at'] = $track->getCreatedAt();
                    }
                }
                $itemobject["tracking_information"] = $trackinginformation;
            } else {
                $itemobject['tracking_information'] = 'notrack';
            }
            
            foreach ($items as $_item) {
//                $itemobject['items'] = $_item->getData();
                $itemresponce['name'] = $_item->getName();
                $options = $_item->getProductOptions();
                $optiondata = array();
                if (isset($options['bundle_options'])) {
                    foreach ($options['bundle_options'] as $key => $value) {
                        $optiondata['options'] = $value;
                        $itemresponce['bundle_options'][] = $optiondata;
                    }
                }
                $allitems[] = $itemresponce;
                $itemobject['items'] = $allitems;
            }
            $response[] = ['Status' => "true", 'message' => $itemobject];
        } else {
            $response[] = ['Status' => "true", 'message' => "No Item found"];
        }
        header('Content-type: application/json');
        echo $json = json_encode($response, JSON_UNESCAPED_SLASHES);
        die();
    }

    public function Createrma(){
       $data = $this->request->getBodyParams(); 
       print_r($data);
       die("test");
    }
}
