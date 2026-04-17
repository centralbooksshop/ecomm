<?php

namespace Retailinsights\Orders\Controller\Adminhtml\Post;

use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    protected $resultRedirectFactory;
    protected $postFactory;
    protected $orderRepository;
    protected $shipmentFactory;
    protected $shipmentRepository;
    public $_storeManager;
    protected $formKey;
    protected $customerSession;
    protected $customerrepository;
    protected $order;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $invoiceRepository;
    protected $searchCriteriaBuilder;
     /**
     * @param Context                                     $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Convert\Order          $convertOrder
     * @param \Magento\Shipping\Model\ShipmentNotifier    $shipmentNotifier
     */
    protected $orderManagement;
   protected $urlBuider;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Retailinsights\Orders\Model\PostFactory $postFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerRepositoryInterface $customerrepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,

        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->postFactory = $postFactory;
        $this->orderRepository = $orderRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->formKey = $formKey;
        $this->_storeManager=$storeManager;
        $this->customerSession = $customerSession;
        $this->customerrepository = $customerrepository; 
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderManagement = $orderManagement;

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $queries = array();
        parse_str($_SERVER['QUERY_STRING'], $queries);
        $url1= $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $return_url = $url1.'cbsadmin/sales/order';
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($return_url);

        $orderId = $queries['id'];
        $shipping_method = $queries['value'];
        $key = $queries['key'];

        if($key == 'cancel_order'){
            if($this->orderManagement->cancel($orderId)){
                 $this->messageManager->addSuccess(__("Order Cancellation Success"));
                return $resultRedirect;
            }else{
                $this->messageManager->addError(__("Can't Cancel this Order"));
                return $resultRedirect;
            }
        }

        if($key == 'shipping' && $shipping_method == 'centralbooksshipping'){

            $logger->info("centreal");
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                if (! $order->canShip()) {
                     die();
                }
                $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
                $shipment = $convertOrder->toShipment($order);

                foreach ($order->getAllItems() AS $orderItem) {
                    if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }

                    $qtyShipped = $orderItem->getQtyToShip();
                    $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                    $shipment->addItem($shipmentItem);
                }
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);
                try {
                    $shipment->save();
                    $shipment->getOrder()->save();
                    $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                        ->notify($shipment);
                    if($shipment->save()){
                        $this->messageManager->addSuccess(__("Order Shipment Success"));
                    }else{
                        $this->messageManager->addError(__("Can't Ship this Order"));
                    }
                } catch (\Exception $e) {
                    
                    return $resultRedirect;
                }
                 
            return $resultRedirect;
        }

        if($key == 'invoice_id'){
            $invoice_id = $queries['invoice_id'];
            
            $collection= $this->postFactory->create()->getCollection();
                $count = 0;
                $id = 0;
                $logger->info($collection->getData()); 
                foreach ($collection as  $value) {
                        if($value['invoice_id'] == $invoice_id){
                            $id = $value['id'];
                            $count =  $value['invoice_count'] + 1;
                        }else{
                            $id = 0;
                        }
                }
            $invoice_url =  $url1.'cbsadmin/sales/order_invoice/print/invoice_id/'.$invoice_id.'/key/';
            
            if($invoice_id!=''){
                if($this->_redirect($invoice_url)){
                    if($id > 0){

                         $mandeetotcol = $this->postFactory->create();
                         $mandeetotcol->setId($id);
                         $mandeetotcol->setInvoiceCount($count);
                         if($mandeetotcol->save()){

                         }

                    }else{
                         $mandeetotcol = $this->postFactory->create();

                         $mandeetotcol->setOrderId($orderId);
                         $mandeetotcol->setInvoiceId($invoice_id);
                         $mandeetotcol->setInvoiceCount(1);
                         if($mandeetotcol->save()){

                         
                         }
                    }
                }  
                
            } else{
                echo 'cant ship this order';
            }

        }
        return;//$this->_pageFactory->create();

    }
}