<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Adminhtml\OrderTransaction;

use Webkul\DeliveryBoy\Helper\Data as DeliveryBoyDataHelper;
use Psr\Log\LoggerInterface;
use Webkul\DeliveryBoy\Controller\RegistryConstants;
use Webkul\DeliveryBoy\Model\OrderTransaction\Source\Status as OrderTransactionStatus;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Close extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\DeliveryBoy\Api\OrderTransactionRepositoryInterface $orderTransactionRepository
     * @param DeliveryboyDataHelper $deliveryboyDataHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Webkul\DeliveryBoy\Api\OrderTransactionRepositoryInterface $orderTransactionRepository,
        DeliveryboyDataHelper $deliveryboyDataHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->deliveryboyDataHelper = $deliveryboyDataHelper;
        $this->logger = $logger;
    }

    /**
     * Close Order Transaction.
     *
     * @return Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderTransactionId = $this->getRequest()->getParam("id");
        $orderId = $this->getRequest()->getParam("order_id");
        if (!empty($orderTransactionId)) {
            try {
                $orderTransaction = $this->orderTransactionRepository->get($orderTransactionId);
                $isClosed = $orderTransaction->getIsClosed();
                if (!$isClosed) {
                    $orderTransaction->setIsClosed(OrderTransactionStatus::IS_CLOSED_YES);
                    $this->orderTransactionRepository->save($orderTransaction);
                    $this->messageManager->addSuccess(__("Transaction has been successfully closed."));
                } else {
                    $this->messageManager->addSuccess(__("Transaction is already in closed state."));
                }
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("sales/order/view", [
            'order_id' => $orderId
        ]);
    }

    /**
     * Authorize current Admin USer.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_DeliveryBoy::deliveryboyorders");
    }
}
