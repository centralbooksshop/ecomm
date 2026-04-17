<?php
 
namespace Retailinsights\WalkinCustomers\Controller\Adminhtml\ProcessOtherCouriers;
 
// use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\Order;
 
class AssignOtherCouriers extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $resultJsonFactory;

    public function __construct(
        \Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory $ProcessCBOOrdersFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context
    )
    {
        $this->ProcessCBOOrdersFactory = $ProcessCBOOrdersFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        // $this->helperData = $helperData;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $incrementIds = $this->getRequest()->getPost('orderIds');
        $trackingTitle = $this->getRequest()->getPost('trackingTitle');
        $trackingNumber = $this->getRequest()->getPost('trackingNumber');

        foreach($incrementIds as $key => $value){
            // if(trim($value) == 'Assign Couriers'){
                $result[$value] = $this->saveOtherCouriers(trim($value), trim($trackingTitle), trim($trackingNumber));
            // }
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }

    public function saveOtherCouriers($incrementId, $trackingTitle, $trackingNumber)
    {
    //     // get order id from increment id
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);

        $orderId = $orderInfo->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);

        if($order->getId()){

            $model = $this->ProcessCBOOrdersFactory->create();
            $model->addData([
                "order_id" => $orderId,
                "driver_id" => '',
                "tracking_title" =>$trackingTitle,
                "tracking_number" =>$trackingNumber
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
            }else{
                return 'failure';
            }
        }else{
            return 'failure';
        }
    }
}