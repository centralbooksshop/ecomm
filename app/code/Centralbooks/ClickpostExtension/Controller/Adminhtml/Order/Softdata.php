<?php

namespace Centralbooks\ClickpostExtension\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

class Softdata extends \Magento\Framework\App\Action\Action
{

    protected $orderRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $paramsToSend = [];
        $selected = $this->getRequest()->getParam('selected');

        $resultRedirect = $this->resultRedirectFactory->create();
        $no_of_selection = count($selected);
        $isSyncedOrNot = false;
        if ($no_of_selection <= 1) {
            foreach ($selected as $orderId) {
                $order = $this->orderRepository->get($orderId);
                if (!empty($order['shipsy_reference_numbers'])) {
                    $isSyncedOrNot = true;
                    $errorexclude = $order['increment_id'] . ' already synced';
                    $this->messageManager->addError(__('Failed to sync: '. $errorexclude));
                }
            }

            if ($isSyncedOrNot) {
                return $resultRedirect->setRefererOrBaseUrl();
            }
        } else {
            $this->messageManager->addError(__('Cannot bulk sync orders'));
            return $resultRedirect->setRefererOrBaseUrl();
        }
        $orderID = json_encode($selected);
        $paramsToSend = ["id" => $orderID];
        return $resultRedirect->setPath('clickpost/softdatashipsy/index', $paramsToSend);
    }
}
