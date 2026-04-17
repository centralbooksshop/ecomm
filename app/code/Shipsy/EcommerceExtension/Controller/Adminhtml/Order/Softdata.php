<?php

namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class Softdata extends Action
{
    protected $orderRepository;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $paramsToSend = [];
        $selected = $this->getRequest()->getParam('selected');
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($selected) || !is_array($selected)) {
            $this->messageManager->addError(__('No orders selected for sync.'));
            return $resultRedirect->setRefererOrBaseUrl();
        }

        $no_of_selection = count($selected);
        $isSyncedOrNot = false;

        if ($no_of_selection <= 1) {
            foreach ($selected as $orderId) {
                $order = $this->orderRepository->get($orderId);

                // Only allow orders with status = assigned_to_picker
                if ($order->getStatus() !== 'assigned_to_picker') {
                    $this->messageManager->addError(__(
                        'Order #%1 cannot be synced because its status is "%2". Only orders with status "assigned_to_picker" can be synced.',
                        $order->getIncrementId(),
                        $order->getStatus()
                    ));
                    return $resultRedirect->setRefererOrBaseUrl();
                }

                // Skip if already synced
                if (!empty($order->getData('shipsy_reference_numbers'))) {
                    $isSyncedOrNot = true;
                    $errorexclude = $order->getIncrementId() . ' already synced.';
                    $this->messageManager->addError(__('Failed to sync: ' . $errorexclude));
                    return $resultRedirect->setRefererOrBaseUrl();
                }
            }

            if ($isSyncedOrNot) {
                return $resultRedirect->setRefererOrBaseUrl();
            }
        } else {
            $this->messageManager->addError(__('Cannot bulk sync orders — please select only one order.'));
            return $resultRedirect->setRefererOrBaseUrl();
        }

        // If everything is valid, proceed to softdata sync
        $orderID = json_encode($selected);
        $paramsToSend = ["id" => $orderID];

        return $resultRedirect->setPath('softdatasync/softdatashipsy/index', $paramsToSend);
    }
}
