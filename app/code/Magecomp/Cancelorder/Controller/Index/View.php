<?php
namespace Magecomp\Cancelorder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Backend\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magecomp\Cancelorder\Model\CancelorderFactory;
use Magecomp\Cancelorder\Helper\Data;
use \Magento\Sales\Model\Order\CreditmemoFactory;
use \Magento\Sales\Model\Order\Invoice;
use \Magento\Sales\Model\Service\CreditmemoService;
class View extends \Magento\Framework\App\Action\Action
{
    protected $_request;
    protected $_inlineTranslation;
    protected $_storeManager;
    protected $_transportBuilder;
    protected $_orderObj;
    protected $_customerModel;
    protected $_backendSession;
    protected $_cancelOrderFactory;
    protected $_cancelOrdereHelper;
    protected $creditmemoService;
    protected $creditmemoFactory;
    protected $invoice;

    public function __construct(
        Context $context,
        Order $orderObj,
        Customer $customerModel,
        Session $backendSession,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Http $request,
        StoreManagerInterface $storeManager,
        CancelorderFactory $cancelorderFactory,
        Data $cancelOrderHelper,
        CreditmemoFactory $creditmemoFactory,
        Invoice $invoice,
        CreditmemoService $creditmemoService
    ) {
        parent::__construct($context);
        $this->_orderObj = $orderObj;
        $this->_customerModel = $customerModel;
        $this->_backendSession = $backendSession;
        $this->_cancelOrderFactory = $cancelorderFactory;
        $this->_cancelOrdereHelper = $cancelOrderHelper;
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->invoice = $invoice;
    }
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        try
        {
            $postObject = new DataObject();
            $orderId = $this->_request->getParam('order_id');
            $order = $this->_orderObj->load($orderId);
            $customerId = $order->getCustomerId();
            if(!empty($customerId))
            {
                $customerData = $this->_customerModel->load($customerId);
                $customerName = $customerData->getFirstname() . ' ' . $customerData->getLastname();
                $customerEmail = $customerData->getEmail();
            }
            else
            {
                $customerName = $order->getBillingAddress()->getFirstName().' '.$order->getBillingAddress()->getLastName();
                $customerEmail = $order->getCustomerEmail();
            }
            if ($order->canCancel() || $order->hasInvoices())
            {
                $order->cancel();
                $comment = '';
                if($this->_cancelOrdereHelper->isCommentEnabled()) {
                    if (isset($data['cancel_reasons'])) {
                        $comment = $data['cancel_reasons'];
                    }
                    if($comment != '')
                    {
                        $comment .= " - ";
                    }
                    if (isset($data['comment'])) {
                        $comment .= $data['comment'];
                    }
                }
                    $modelCancelOrder = $this->_cancelOrderFactory->create();
                    if($order->hasInvoices())
                    {
                        $newstatus=ucfirst("canceled");
                        $invoices = $order->getInvoiceCollection();
                        foreach ($invoices as $invoice) {
                            $invoiceincrementid = $invoice->getIncrementId();
                        }
                        if($invoiceincrementid) {
                            $creditmemo = $this->creditmemoFactory->createByOrder($order);
                            $this->creditmemoService->refund($creditmemo);
                            $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                        }
                    }else{
                        $newstatus=ucfirst($order->getStatus());
                    }
                    $modelCancelOrder->setOrderId($order->getIncrementId())
                        ->setCustomerEmail($customerEmail)
                        ->setStatus($newstatus)
                        ->setComment($comment)
                        ->save();

                    $order->addStatusHistoryComment($customerName . ' (customer)\'s canceled with reason to: ' . $comment);


                $order->save();
                $realOrderid = $order->getRealOrderId();
                $result = compact("realOrderid", "customerName", "customerEmail", "comment");
                $postObject->setData($result);

                $this->_inlineTranslation->suspend();

                $transport = $this->_transportBuilder->setTemplateIdentifier($this->_cancelOrdereHelper->getAdminEmailTemplate())
                    ->setTemplateOptions(
                                [
                                    'area' => Area::AREA_FRONTEND,
                                    'store' => $this->_storeManager->getStore()->getId(),
                                ]
                        )->setTemplateVars(['data' => $postObject])
                        ->setFrom($this->_cancelOrdereHelper->getEmailSender())
                        ->addTo($this->_cancelOrdereHelper->getAdminEmailRecipient())
                        ->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                $this->_inlineTranslation->suspend();

                $transport = $this->_transportBuilder->setTemplateIdentifier($this->_cancelOrdereHelper->getCustomerEmailTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                )->setTemplateVars(['data' => $postObject])
                ->setFrom($this->_cancelOrdereHelper->getEmailSender())
                ->addTo($customerEmail)
                ->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                $this->messageManager->addSuccess(__('Your Order has been Canceled successfully.'));
                $this->_backendSession->setFormData(false);
                return $resultRedirect->setRefererUrl();
            }
            else
            {
                $this->messageManager->addSuccess(__('Something went wrong!'));
            }
        }
        catch (\Magento\Framework\Exception\LocalizedException $e)
        {
            $this->messageManager->addError($e->getMessage());
        }
        catch (\RuntimeException $e)
        {
            $this->messageManager->addError($e->getMessage());
        }
        catch (\Exception $e)
        {
            $this->messageManager->addException($e, __('Something went wrong!'));
        }
        return $resultRedirect->setRefererUrl();
    }
}
