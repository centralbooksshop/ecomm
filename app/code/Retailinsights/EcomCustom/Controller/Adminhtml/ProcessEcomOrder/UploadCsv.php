<?php
namespace Retailinsights\EcomCustom\Controller\Adminhtml\ProcessEcomOrder;

use Magento\Framework\Controller\ResultFactory;

class UploadCsv extends \Magento\Backend\App\Action
{
   protected $resultRedirectFactory;
    private $fedexLabels;
    protected $orderRepository;
    protected $_coreRegistry;
    protected $csv;
    protected $resultPageFactory;
    protected $jsonHelper;
    protected $resultFactory;
    protected $messageManager;
    protected $eavConfig;
    protected $resultJsonFactory;

    public function __construct(
      \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
      \Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory $fedexLabels,
	  \Ecom\Ecomexpress\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
      \Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory $invoiceResourceCountCollection,
      \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
      \Magento\Framework\Registry $coreRegistry,
      \Magento\Framework\File\Csv $csv,
      \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->fedexLabels = $fedexLabels;
		$this->collectionFactory = $collectionFactory;
        $this->invoiceResourceCountCollection = $invoiceResourceCountCollection;
        $this->orderRepository = $orderRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->csv = $csv;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eavConfig = $eavConfig;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }
    public function execute()
    {
      try{
         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('core_config_data');
        $sql = $connection->select()->from($tableName)->where('path = ?', 'admin/url/custom');
        $result = $connection->fetchAll($sql);  
        $url1 = $result[0]['value'];
        $return_url = $url1.'cbsadmin/ecomcustom/ProcessEcomOrder';

        $handle = fopen($_FILES['csv']['tmp_name'], "r");
        $fileTypeAllowed = ['application/vnd.ms-excel','text/csv'];
        
        if(!in_array($_FILES['csv']['type'], $fileTypeAllowed)){
          $message = __('Please check file format (allowed format csv)');
          $this->messageManager->addErrorMessage($message);

          $resultRedirect = $this->resultRedirectFactory->create();
          $resultRedirect->setPath($return_url);
          return $resultRedirect;
        }
        
        $headers = fgetcsv($handle, 1000, ",");
        $count=0;
        $collection = array();
        $collection_type_2 = array();

        while (($data_val = fgetcsv($handle, 1000, ",")) !== FALSE) {
          if($data_val['0'] == ''){
            // $key = $key+1;
            $count = $count+1;
            $message = __('Cant be empty at line :'.$count);
            $this->messageManager->addErrorMessage($message);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($return_url);

            return $resultRedirect;
          }else{
            $count = $count+1;
          }
            // $collection[] = "<h1>".$data_val[0]."</h1>";
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order_id = trim($data_val[0]);
            $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order_id);

            $status = $orderInfo->getStatus();
            $orderId = $orderInfo->getId();

              if(!$orderId){
                  $collection[] ='<span>'.$order_id.': Processing / Courier selection may be pending</span><br/>';
              }
                if($status == 'dispatched_to_courier'){
                  $collection[]= '<span>'.$order_id.' : dispatched_to_courier / Courier selection may be pending</span><br/>';
                }
                if($status == "order_delivered"){
                  $collection[]= '<span>'.$order_id.' : order_delivered / Courier selection may be pending</span><br/>';
                
                }
                if(($status == 'complete') || ($status == 'pending') || ($status == 'processing')){
                   $order = $this->orderRepository->get($orderId);
                   $shipment = $order->getShipmentsCollection();
                    $flag = $this->checkPayslipGenerated($orderInfo);
                     $isLabel = $this->checkEcomLabel($orderInfo);

                    if($isLabel == 'yes'){
                      $collection[]= '<span>'.$order_id.' : Courier tracking info not exists</span><br/>';

                    }elseif(empty($shipment->getData())){
                      $collection[]= '<span>'.$order_id.' : Processing / Courier selection may be pending</span><br/>';
                    }elseif($flag == 'no'){
                      $collection[]= '<span>'.$order_id.' : Package slip not downloaded</span><br/>';
                    }else{
                       $collection[]='<li style="list-style: none"><label><span id="orderIdList" class="valid_ids">'.$order_id.'</span></label></li><br/>';

                    }
                }

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($return_url);
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $persistor = $om->get('Magento\Framework\App\Request\DataPersistorInterface');
        $persistor->set('coloroptionEcom', $collection);

        return $resultRedirect;
        // return $this->resultFactory->create();


        }catch (\Exception $e) {
          print_r($e->getMessage());
        }

    }
    
	public function checkPayslipGenerated($order)
    {
       
        $orderId = $order->getId();
        $invoice_id = '';
        foreach ($order->getInvoiceCollection() as $invoice)
        {
            $invoice_id = $invoice->getId();
        }
        $collection = $this->invoiceResourceCountCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('invoice_id', $invoice_id);
        if($collection){
            foreach ($collection as  $value) {
                $invoice = trim($value['invoice_id']);
                $invoice_id = trim($invoice_id);
    
                if($invoice == $invoice_id){
                    return 'yes';
                }else{
                    return 'no';
                }
            }       
        }
        return 'no';
    }
    

	public function checkEcomLabel($order)
    {
        $orderId = $order->getId();
        $collection = $this->collectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('orderid', $orderId);
           // ->addFieldToFilter('lstatus', '0');
        if(!empty($collection->getFirstItem()->getData('orderid'))){
            return 'yes';
        }
        return 'no';
    }
}



