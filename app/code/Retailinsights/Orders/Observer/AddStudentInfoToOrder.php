<?php

namespace Retailinsights\Orders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
// use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
// use Magento\Catalog\Model\ProductCategoryList;

 
 
class AddStudentInfoToOrder implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * 
     * 
     */

    // protected $_cart;
    protected $_checkoutSession;
    protected $registry;
    private $logger;
    private $_request;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->_request = $request;
        $this->registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/addstudentinfoorder.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
 
		if($this->getWebsiteCode() == 'schools'){
            $quoteId = $this->_checkoutSession->getQuote()->getId();
            $quote = $this->quoteRepository->get($quoteId);
			
            if($quote) {
				//print_r ($this->_checkoutSession->getSessionId());
				//print_r($this->_checkoutSession->getData());
				//$logger->info('Quote Array Log '.print_r($quote->debug(), true));
				$orderItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
                $orderItemcount = 0;
                $orderItemcount = count($orderItems);
				
                $logger->info("orderItemcount ". $orderItemcount);
				$logger->info("roll_no ". $quote->getRollNo());
				$logger->info("student_name ". $quote->getStudentName());
				$logger->info("school_id ". $quote->getSchoolId());
				$logger->info("school_name ". $quote->getSchoolName());
				$logger->info("school_code ". $quote->getSchoolCode());
				$logger->info("location_code ". $quote->getLocationCode());
				$logger->info("product_purchased ". $quote->getProductPurchased());

			    // save to sales_order
                $order = $observer->getEvent()->getOrder();
				$order->setData('roll_no', $quote->getRollNo());
                $order->setData('student_name', $quote->getStudentName());
                $order->setData('school_id', $quote->getSchoolId()); 
                $order->setData('school_name', $quote->getSchoolName()); 
                $order->setData('school_code', $quote->getSchoolCode());
				$order->setData('location_code', $quote->getLocationCode()); 
				$order->setData('product_purchased', $quote->getProductPurchased());
                if(!empty($orderItemcount) && $orderItemcount > 1) {
                   $order->setData('order_multiple_status', 'success'); 
				}
                $order->save();
            }
        } else if($this->getWebsiteCode() == 'base') {
            $quoteId = $this->_checkoutSession->getQuote()->getId();
			$this->logger->info('quoteId '.$quoteId); 
            $quote = $this->quoteRepository->get($quoteId);
			if($quote) {
               $productName = [];
				$allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
				foreach ($allItems as $key => $item) {
					$productId = $item->getProductId();
					$productName[] = $item->getName();
					
				}
				$product_purchased = implode(',', $productName);
				$this->logger->info('product_purchased Log'.$product_purchased);
				//$location_code = 'Hyderabad';

				$order = $observer->getEvent()->getOrder();
				$order->setData('product_purchased', $product_purchased);
				//$order->setData('location_code', $location_code); 
                $order->save();
			}
		}
    }

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }
    
}