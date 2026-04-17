<?php

namespace Retailinsights\Orders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Phrase;

 
 
class RestrictAddToCart implements ObserverInterface
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
    // protected $productRepository;
    protected $registry;
    // protected $_storeManager;
    // protected $productCategory;
    private $logger;
    // private $customerSession;
    private $_request;
    private $collectionFactory;
    protected $_messageManager;
    protected $redirect;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    )
    {
        $this->_request = $request;
        $this->registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->getWebsiteId() == '2'){
        //     $student_name = $this->_request->getPost('student_pid');
        //     $student_number = $this->_request->getPost('student_pname');
         //      $this->logger->info('name: '.$student_name);

        //     if(($student_name == '') && ($student_number == '')) {
        //             $this->_messageManager->addError(__('Incomplete information'));
        //             //set false if you not want to add product to cart
        //             $observer->getRequest()->setParam('product', false);
        //         // return $this;
        //         $errormsg = 'incomplete info';
        //         $queryName = 'product error';
        //         throw new StateException(
        //             new Phrase($errormsg, [$queryName])
        //            ); 
        //         // return;
        //             // return $this->resultRedirectFactory->create()->setPath('*/*/');
        //     }
        }
    }

    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }
    
}