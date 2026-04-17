<?php

namespace Magecomp\Cancelorder\Cron;

use Magento\Framework\App\Area;
use Magento\Framework\DataObject;

class Duretion
{
    protected $_orderCollectionFactory;
    protected $helperData;
    protected $orderManagement;
    protected $_orderObj;
    protected $invoice;
    protected $logger;
    protected $_storeManager;
    protected $creditmemoFactory;
    protected $creditmemoService;
    protected $_customerModel;
    protected $_cancelOrderFactory;
    protected $_inlineTranslation;
    protected $_transportBuilder;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magecomp\Cancelorder\Helper\Data $helperData,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Order $orderObj,
        \Magento\Customer\Model\Customer $customerModel,
        \Psr\Log\LoggerInterface $logger,
        \Magecomp\Cancelorder\Model\CancelorderFactory $cancelorderFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->helperData = $helperData;
        $this->orderManagement = $orderManagement;
        $this->_orderObj = $orderObj;
        $this->logger = $logger;
        $this->_customerModel = $customerModel;
        $this->_cancelOrderFactory = $cancelorderFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
    }

    public function execute()
    {
        if ($this->helperData->isEnabled() && $this->helperData->isAutoEnabled()) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/magecampcancelordertracking.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Magecomp Cancel Order cron is started ".date("Y-m-d H:i:s"));
        $startMicro = microtime(true);
        $startAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $processed  = 0;
            try {
                $postObject = new DataObject();
                foreach ($this->helperData->getPaymentandtime() as $paymentwithtimes) {
                    if ($paymentwithtimes['velidates'] == 1) {
                        $to = date('Y-m-d') . ' 23:59:59';
                        $from = date('Y-m-d', strtotime(-$paymentwithtimes['time'] . 'days')) . ' 00:00:00';
                    } else {
                        $time = time();
                        $totaltimes = 60 * 60 * $paymentwithtimes['time'];
                        $to = date('Y-m-d H:i:s', $time);
                        $lastTime = $time - $totaltimes;
                        $from = date('Y-m-d H:i:s', $lastTime);
                    }
                    $collection = $this->_orderCollectionFactory->create()->addFieldToSelect(array('entity_id', 'status'))
                        ->addAttributeToFilter('status', ['in' => explode(",", $this->helperData->getPaymentstatus())])
                        ->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to));
                          $logger->info("Magecomp Cancel Order no of orders processed is  ".count($collection->getData()));
                        if (count($collection->getData()) > 0) {
                        foreach ($collection->getData() as $items) {
                            $order = $this->_orderObj->load($items['entity_id']);
                            if ($this->helperData->isEnabledadmin($order->getStoreId())) {
                                $payments = $order->getPayment()->getMethod();
                                if ($payments == $paymentwithtimes['paymentmethod']) {
                                    $this->orderManagement->cancel($items['entity_id']);
                                    $customerId = $order->getCustomerId();
                                    if (!empty($customerId)) {
                                        $customerData = $this->_customerModel->load($customerId);
                                        $customerName = $customerData->getFirstname() . ' ' . $customerData->getLastname();
                                        $customerEmail = $customerData->getEmail();
                                    } else {
                                        $customerEmail = $order->getCustomerEmail();
                                        $customerName = $order->getBillingAddress()->getFirstName() . ' ' . $order->getBillingAddress()->getLastName();
                                    }
                                    $comment = ' - ';
                                    $newstatus = ucfirst("canceled");
                                    $modelCancelOrder = $this->_cancelOrderFactory->create();
                                    $modelCancelOrder->setOrderId($order->getIncrementId())
                                        ->setCustomerEmail($customerEmail)
                                        ->setStatus($newstatus)
                                        ->setComment($comment)
                                        ->save();
                                    $realOrderid = $order->getRealOrderId();
                                    $result = compact("realOrderid", "customerName", "customerEmail", "comment");
                                    $postObject->setData($result);
                                    try {
                                        $this->_inlineTranslation->suspend();
                                        $transport = $this->_transportBuilder->setTemplateIdentifier($this->helperData->getAdminEmailTemplate())
                                            ->setTemplateOptions(
                                                [
                                                    'area' => Area::AREA_FRONTEND,
                                                    'store' => $this->_storeManager->getStore()->getId(),
                                                ]
                                            )->setTemplateVars(['data' => $postObject])
                                            ->setFrom($this->helperData->getEmailSender())
                                            ->addTo($this->helperData->getAdminEmailRecipient())
                                            ->getTransport();
                                        $transport->sendMessage();
                                        $this->_inlineTranslation->resume();
                                        $this->_inlineTranslation->suspend();
                                        $transport = $this->_transportBuilder->setTemplateIdentifier($this->helperData->getCronEmailTemplate())
                                            ->setTemplateOptions(
                                                [
                                                    'area' => Area::AREA_FRONTEND,
                                                    'store' => $this->_storeManager->getStore()->getId(),
                                                ]
                                            )->setTemplateVars(['data' => $postObject])
                                            ->setFrom($this->helperData->getEmailSender())
                                            ->addTo($customerEmail)
                                            ->getTransport();
                                        $transport->sendMessage();
                                        $this->_inlineTranslation->resume();
                                    } catch (\Exception $e) {
                                        $this->logger->critical($e->getMessage());
                                    }
                                }
                            }
                            $processed++;
                        }
                    }
                }
        $endMicro = microtime(true);
        $endAt    = new \DateTime('now', new \DateTimeZone('UTC'));
        $logger->info('Magecomp Cancel Order END AT ' . $endAt->format('Y-m-d H:i:s') . ' duration_sec: ' . round($endMicro - $startMicro, 3).
            ' processed records '. $processed);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
