<?php
 
namespace Webkul\DeliveryBoy\Controller\Adminhtml\ProcessCBOShippingOrders;
 
// use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\Order;
 
class AssignDriver extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $resultJsonFactory;
    protected $ProcessCBOOrdersFactory;
    protected $ListautodriversFactory;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Retailinsights\ProcessCBOOrders\Model\ProcessCBOOrdersFactory $ProcessCBOOrdersFactory,
        \Retailinsights\Autodrivers\Model\ListautodriversFactory $ListautodriversFactory,   
        \Retailinsights\SmsOnOrderStatusChange\Helper\Data $helperData,
        Context $context
    )
    {
        $this->ListautodriversFactory = $ListautodriversFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->ProcessCBOOrdersFactory = $ProcessCBOOrdersFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $incrementIds = $this->getRequest()->getPost('orderIds');
        $driverId = $this->getRequest()->getPost('driverId');

        foreach($incrementIds as $key => $value){
            // if(trim($value) == 'Assign Couriers'){
                $result[$value] = $this->saveOrderDriver(trim($value), trim($driverId));
            // }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }

    public function saveOrderDriver($incrementId, $driverId)
    {
        // get order id from increment id
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);

        $orderId = $orderInfo->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
        if($order->getId()){
            // save orders with drivers
            $model = $this->ProcessCBOOrdersFactory->create();
			if($orderId != '' && $driverId != '' ) {
            $model->addData([
                "order_id" => $orderId,
                "driver_id" => $driverId,
                "tracking_title" =>'',
                "tracking_number" =>''
                ]);
            
                $saveData = $model->save();
            }
            if($saveData){
                $state = $order->getState();
                $status = 'dispatched_to_courier';
                $comment = '';
                $isNotified = false;
                    $order->setState($state);
                    $order->setStatus($status);
                    $order->addStatusToHistory($order->getStatus(), $comment);
                    $order->save(); 
                    $msg = $this->SendMessageAssignDriver($order);
                
                return 'success';
            }else{
                return 'failure';
            }
        }else{
            return 'failure';
        }

    }

    public function SendMessageAssignDriver($order){
        $model = $this->ProcessCBOOrdersFactory->create()->getCollection();
        $modelDriver = $this->ListautodriversFactory->create()->getCollection();
        $driver='';
        $driverMobile='';
        foreach ($model as $value) {
            if($value->getOrderId() == $order->getId()){
                $driver_id = $value->getDriverId();
                foreach ($modelDriver as $valueDriver) {
                    if($value->getDriverId() == $valueDriver->getId()){
                        $driver = $valueDriver->getDriverName(); 
                        $driverMobile = $valueDriver->getDriverMobile(); 
                    }
                }
            }
        }
        $incrementId =$order->getIncrementId();
        $custmerName = $order->getShippingAddress()->getData('firstname');
        
        $msg = "Dear ".$custmerName.", Your order ".$incrementId." was picked up by ".$driver." ".$driverMobile.". You will receive the order in 3 working days, if any queries mail us at: help@centralbooksonline.com";
        
        $mobile = $order->getShippingAddress()->getTelephone();
        
        $sms = $this->helperData->SendSms($msg,"Y",$mobile);
        if($sms==''){
            return "sms sent successfully";
        }else{
            return "sms service error";
        }  
    }
}
