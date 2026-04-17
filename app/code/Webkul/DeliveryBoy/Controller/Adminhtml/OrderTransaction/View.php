<?php

namespace Webkul\DeliveryBoy\Controller\Adminhtml\OrderTransaction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Webkul\DeliveryBoy\Api\OrderTransactionRepositoryInterface;
use Webkul\DeliveryBoy\Controller\RegistryConstants;

class View extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registory
     */
    protected $registry;

    /**
     * @var OrderTransactionRepositoryInterface
     */
    protected $orderTransactionRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param OrderTransactionRepositoryInterface $orderTransactionRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        OrderTransactionRepositoryInterface $orderTransactionRepository
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->orderTransactionRepository = $orderTransactionRepository;
    }

    /**
     * Render Order Transaction.
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $request = $this->getRequest();
        $transactionEntityId = $request->getParam('id');
        $orderTransaction = $this->orderTransactionRepository->get($transactionEntityId);
        $this->registry->register(RegistryConstants::CURRENT_ORDER_TRANSACTION, $orderTransaction);
        $transactionId = $orderTransaction->getTransactionId();
        $resultPage->getConfig()->getTitle()->append(__(' ') . $transactionId);

        return $resultPage;
    }
}
