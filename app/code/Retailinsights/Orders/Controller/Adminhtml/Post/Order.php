<?php

namespace Retailinsights\Orders\Controller\Adminhtml\Post;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class Order extends \Magento\Backend\App\Action {
 
  /**
   * @var OrderRepositoryInterface
   */
  protected $orderRepository;
 
  /**
   * @var InvoiceService
   */
  protected $invoiceService;
 
  /**
   * @var TransactionFactory
   */
  protected $transactionFactory;

  /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
	protected $request;
 
 
  public function __construct(
    OrderRepositoryInterface $orderRepository,
    InvoiceService $invoiceService,
    TransactionFactory $transactionFactory,
	InvoiceRepositoryInterface $invoiceRepository,
	\Magento\Backend\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
	\Magento\Framework\App\Request\Http $request
  )
  {
    $this->orderRepository = $orderRepository;
    $this->invoiceService = $invoiceService;
    $this->transactionFactory = $transactionFactory;
	$this->invoiceRepository = $invoiceRepository;
	$this->resultPageFactory = $resultPageFactory;
	$this->request = $request;
	parent::__construct($context);
        
  }
 
  public function execute()
  {
    /*$invoiceId = $this->request->getParam('invoiceid');
	if(!empty($invoiceId)) {
		 $deleteInvoice = false;
		 $invoiceData = $this->invoiceRepository->get($invoiceId);
		 $deleteInvoice = $this->invoiceRepository->delete($invoiceData);
		 echo 'Invoice deleted ' ;
	}*/
	$orderId = $this->request->getParam('orderid');
	if(!empty($orderId)) {
        $order = $this->orderRepository->get($orderId);
        //if ($order->canInvoice()) {
        if (!$order->hasInvoices()) {
			  //$invoice = $order->prepareInvoice();
			  $invoice = $this->invoiceService->prepareInvoice($order);
			  $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
			  $invoice->register();
			  $invoice->getOrder()->setCustomerNoteNotify(false);
			  $invoice->getOrder()->setIsInProcess(true);
			  $order->addCommentToStatusHistory(__('Automatically INVOICED'), false);
			  $transactionSave = $this->transactionFactory->create();
			  $transactionSave->addObject($invoice)->addObject($invoice->getOrder());
			  $transactionSave->save();
			   if ($order->getShipmentsCollection()->count()) {
					try {
						$order->setState('complete')->setStatus('complete');
						$order->addStatusToHistory($order::STATE_COMPLETE, 'Order has been paid.', true);
					} catch (Exception $exception) {
					}
				} else {
					$order->setState($order::STATE_PROCESSING)->save();
					$order->setStatus($order::STATE_PROCESSING)->save();
					$order->addStatusToHistory($order::STATE_PROCESSING, 'Order has been paid.', true);
				}
				/* reset total_paid & base_total_paid of order */
                //$order->setTotalPaid($order->getTotalPaid() - $invoice->getGrandTotal());
               // $order->setBaseTotalPaid($order->getBaseTotalPaid() - $invoice->getBaseGrandTotal());
				
				$order->setTotalPaid($order->getGrandTotal());
                $order->setBaseTotalPaid($order->getBaseGrandTotal());
				$this->orderRepository->save($order);		
			     echo 'invoice created' ;
		} else {
			 if ($order->getShipmentsCollection()->count()) {
					try {
						$order->setState('complete')->setStatus('complete');
						$order->addStatusToHistory($order::STATE_COMPLETE, 'Order has been paid.', true);
					} catch (Exception $exception) {
					}
				} else {
					$order->setState($order::STATE_PROCESSING)->save();
					$order->setStatus($order::STATE_PROCESSING)->save();
					$order->addStatusToHistory($order::STATE_PROCESSING, 'Order has been paid.', true);
				}
             $order->setTotalPaid($order->getGrandTotal());
             $order->setBaseTotalPaid($order->getBaseGrandTotal());
			 $this->orderRepository->save($order);
		  echo 'invoice already exist' ;
		}
	}


  }
 
}