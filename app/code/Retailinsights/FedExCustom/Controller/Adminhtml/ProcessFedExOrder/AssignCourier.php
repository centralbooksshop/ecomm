<?php
 
namespace Retailinsights\FedExCustom\Controller\Adminhtml\ProcessFedExOrder;
 
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\Order;
 
class AssignCourier extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $resultJsonFactory;
    private $fedexLabels;

    public function __construct(
        \Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory $fedexLabels,
        \Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory $ProcessCBOOrdersFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context
    )
    {
        $this->fedexLabels = $fedexLabels;
        $this->ProcessCBOOrdersFactory = $ProcessCBOOrdersFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $incrementIds = $this->getRequest()->getPost('orderIds');

        // foreach($incrementIds as $key => $value){
        //     $result[$key] = $this->saveCourier(trim($value));
        // }
        $result = $this->saveCourier(trim($incrementIds));

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }

    public function saveCourier($incrementId)
    {
    //     // get order id from increment id
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);

        $orderId = $orderInfo->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);

        if($order->getId()){
            // get tracking number
            $collection = $this->fedexLabels->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $orderId);
            if(!empty($collection->getFirstItem()->getData('order_id'))){
                $trackingNo = $collection->getFirstItem()->getData('trackingnumber');
            }else{
                $trackingNo = '';
            }

            // save to db
            $model = $this->ProcessCBOOrdersFactory->create();
            $model->addData([
                "order_id" => $orderId,
                "driver_id" => '',
                "tracking_title" =>'FedEx',
                "tracking_number" =>$trackingNo
                ]);

            $saveData = $model->save();
            if($saveData){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
                $state = $order->getState();
                $status = 'dispatched_to_courier';
                $comment = '';
                $isNotified = false;
                $order->setState($state);
                $order->setStatus($status);
                $order->addStatusToHistory($order->getStatus(), $comment);
                $order->save(); 
                
                return 'success';
                // $return_arr[]= array('status' => 'success','value' => $incrementId);
                // echo json_encode($return_arr);
                // die();
            }else{
                return 'failure';
            }
        }else{
            return 'failure';
        }
    }
}