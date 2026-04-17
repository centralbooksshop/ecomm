<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order\ItemFactory;

class MassPartial extends Action
{
    protected $orderModelFactory;

    public function __construct(
        Action\Context $context,
        ItemFactory $orderModelFactory
    ) {
        $this->orderModelFactory = $orderModelFactory;
        parent::__construct($context);
    }

    public function execute()
	{
		$selectedIds = $this->getRequest()->getParam('selected', []);

		if (!is_array($selectedIds) || empty($selectedIds)) {
			$this->messageManager->addErrorMessage(__('Please select items.'));
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}

			try {
			$updatedCount = 0;

			foreach ($selectedIds as $itemId) {
				/** @var \Vendor\Module\Model\Order $orderItem */
				$orderItem = $this->orderModelFactory->create()->load($itemId);

				if (!$orderItem->getId()) {
					$this->messageManager->addWarningMessage(
						__('Item ID %1 skipped: Not found.', $itemId)
					);
					continue;
				}

				$dispatchDate = $orderItem->getData('dispatch_date');

				if (empty($dispatchDate)) {
					$orderItem->setData('partial_delivery_date', date('Y-m-d H:i:s'));
					$orderItem->save();
					$updatedCount++;
				} else {
					$this->messageManager->addNoticeMessage(
						__('Item ID %1 skipped: Already dispatched on %2.', $itemId, $dispatchDate)
					);
				}
			}

			if ($updatedCount > 0) {
				$this->messageManager->addSuccessMessage(
					__('A total of %1 order(s) have been marked as Partial.', $updatedCount)
				);
			} else {
				$this->messageManager->addNoticeMessage(__('No orders were updated.'));
			}
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage(__('Error while processing: %1', $e->getMessage()));
			$this->_logger->error($e->getMessage(), ['exception' => $e]);
		}

		// Redirect to the last (referer) URL dynamically
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;
	}


    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Retailinsights_Orders::mass_partial');
    }
}
