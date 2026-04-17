<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\ItemFactory;

class Confirm extends Action
{
    protected $resource;
    protected $orderItemFactory;
    protected $date;

    public function __construct(
        Action\Context $context,
        ResourceConnection $resource,
        ItemFactory $orderItemFactory,
        DateTime $date
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->orderItemFactory = $orderItemFactory;
        $this->date = $date;
    }

    public function execute()
    {
        $itemIdsParam = (string)$this->getRequest()->getParam('item_id', '');
        $itemIds = $itemIdsParam !== '' ? array_filter(explode(',', $itemIdsParam)) : [];

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('retailinsights_admin/willbegiven/index');

        if (empty($itemIds)) {
            $this->messageManager->addErrorMessage(__('No items selected for confirmation.'));
            return $resultRedirect;
        }

        try {
            $updatedCount = 0;
            foreach ($itemIds as $itemId) {
                $orderItem = $this->orderItemFactory->create()->load($itemId);
                if ($orderItem->getId()) {
                    $orderItem->setData('delivery_date', $this->date->gmtDate());
					$orderItem->setData('delivery_status', 'confirmed');
                    $orderItem->save();
                    $updatedCount++;
                }
            }

            if ($updatedCount > 0) {
                $this->messageManager->addSuccessMessage(
                    __('Order confirmed successfully for %1 item(s).', $updatedCount)
                );
            } else {
                $this->messageManager->addNoticeMessage(__('No valid order items found to update.'));
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error updating order: %1', $e->getMessage()));
        }

        return $resultRedirect;
    }
}
