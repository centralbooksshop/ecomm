<?php

namespace Retailinsights\SmsOnOrderStatusChange\Observer;

use Magento\Framework\Event\ObserverInterface;

use \Magento\Framework\Event\Observer       as Observer;
use \Magento\Framework\View\Element\Context as Context;
/**
 * Customer login observer
 */
class OrderStateChange implements ObserverInterface
{
       /**
     * Https request
     *
     * @var \Zend\Http\Request
     */
    protected $_request;
    protected $variable;
    protected $logger; 
    /**
     * Layout Interface
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Retailinsights\SmsOnOrderStatusChange\Helper\Data $helperData,
        \Magento\Sales\Api\Data\OrderInterface $order,
	\Magento\Variable\Model\Variable $variable,
	\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolsCollection,
	\Psr\Log\LoggerInterface $logger
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_request = $context->getRequest();
        $this->_layout  = $context->getLayout();
        $this->order = $order;
	$this->variable = $variable;
        $this->helperData = $helperData;
        $this->schoolsCollection = $schoolsCollection;
	$this->_storeManager = $storeManager;
	$this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof \Magento\Framework\Model\AbstractModel) {
            $incrementId = $order->getIncrementId(); 
            
			$custmerName = $order->getShippingAddress()->getFirstname();
			$mobile = $order->getShippingAddress()->getTelephone();
			$accountlinkvalue = $this->variable->loadByCode('sms-deliveredreviewlink', 'admin');
			$deliveredreviewlink = $accountlinkvalue->getPlainValue();
			$orderReturnSms = $this->variable->loadByCode('order_return_sms', 'admin');
			$orderReturnSmsData = $orderReturnSms->getPlainValue();
			$orderReturnSmsArray = explode(",", $orderReturnSmsData);
			$schoolName = $order->getSchoolName();
			$storeId = $order->getStoreId(); 
			$this->logger->info('schoolName - '.$schoolName);
			$this->logger->info('orderReturnSmsArray - '.print_r($orderReturnSmsArray,true));
			if (in_array($schoolName, $orderReturnSmsArray)) {
				$this->logger->info('schoolName - '.$schoolName);
				$this->logger->info('orderReturnSmsArray - '.print_r($orderReturnSmsArray,true));
			}

			$schoolDelivery = 0;
            try {
				$resource = \Magento\Framework\App\ObjectManager::getInstance()
					->get(\Magento\Framework\App\ResourceConnection::class);

				$connection = $resource->getConnection();
				$schoolTable = $resource->getTableName('schools_registered');

				$select = $connection->select()
					->from($schoolTable, ['school_delivery'])
					->where('school_name_text = ?', $schoolName)
					->limit(1);

				$schoolDelivery = (int) $connection->fetchOne($select);

			} catch (\Exception $e) {
				$this->logger->error('School delivery fetch failed: ' . $e->getMessage());
			}


			$helpdesklinkvalue = $this->variable->loadByCode('sms-helpdesklinkvalue', 'admin');
			$helpdesklink = $helpdesklinkvalue->getPlainValue();
			if(empty($deliveredreviewlink)) {
			   $deliveredreviewlink ='';
			}
			if(empty($helpdesklink)) {
			   $helpdesklink ='';
			}
			  
            if($order->getStatus() == 'order_delivered'){
               $msg = "Dear ".$custmerName.", Your order ".$incrementId." has been successfully delivered. Reach us at ".$helpdesklink." for any assistance. Kindly leave a review here ".$deliveredreviewlink.".
- centralbooksonline.com.";
				$sms = $this->helperData->AppSendSms($msg,"Y",$mobile);
				if ($storeId == 3) {
				
				  $orderReturnNotification = "Dear ".$custmerName." Please note that the return request must be submitted within 7 business days to avoid delays - Central Books";
						  $sms = $this->helperData->AppSendSms($orderReturnNotification,"Y",$mobile);		 
				}

				if ($schoolDelivery === 1) {
					$school_msg = "Dear " . $custmerName . 
					   ", Your order " . $incrementId . 
					   " has been successfully delivered to your school. " .
					   "Reach us at https://centralbooksonline.com/ for any assistance. " .
					   "- centralbooksonline.";
					$sms = $this->helperData->AppSendSms($school_msg, "Y", $mobile);
				}
            }

            if($order->getStatus() == 'canceled') {
                // $msg = "Dear ".$custmerName.", Status of your order ".$incrementId."  has changed to cancelled . Have a nice day, Central Books Online. https://www.CentralBooksOnline.com/home";
                $msg = "Dear ".$custmerName.",
Status of your order ".$incrementId."  has changed to cancelled. 
if any queries, email us at: help@centralbooksonline.com";
                $sms = $this->helperData->SendSms($msg,"Y",$mobile);
                if($sms==''){
                    echo "sms sent successfully";
                }else{
                    echo "sms service error";
                }  
            }
        }
    }
}            


