<?php

namespace Retailinsights\FedExCustom\Controller\Adminhtml\ProcessedFedexOrdersList;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Retailinsights\FedExCustom\Model\ResourceModel\ProcessFedexOrders\CollectionFactory;

class Delivered extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $statusValue = $this->getRequest()->getParam('status');
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        foreach ($collection as $item) {
            $orderIds = $item->getData();
            $orderId = $orderIds['order_id'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $state = $order->getState();
            $status = 'order_delivered';
            $comment = '';
            $isNotified = false;
            $order->setState($state);
            $order->setStatus($status);
            $order->addStatusToHistory($order->getStatus(), $comment);
            $order->save(); 
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been modified.', $collection->getSize()));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('fedexcustom/processedfedexorderslist/index');
    }
}