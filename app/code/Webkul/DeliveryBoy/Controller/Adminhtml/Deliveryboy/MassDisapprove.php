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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy;

use Webkul\DeliveryBoy\Model\Deliveryboy\Source\ApproveStatus;

class MassDisapprove extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{
    /**
     * Mass disapprove deliveryboys.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection     = $this->filter->getCollection($this->collectionFactory->create());
        $deliveryboysUpdated = 0;
        $coditionArr    = [];
        foreach ($collection->getAllIds() as $deliveryboyId) {
            $currentDeliveryboy = $this->deliveryboyRepository->getById($deliveryboyId);
            $deliveryboyData = $currentDeliveryboy->getData();
            if (count($deliveryboyData) &&
                $currentDeliveryboy->getApproveStatus() == ApproveStatus::STATUS_APPROVED
            ) {
                $condition = "`id`=" . $deliveryboyId;
                array_push($coditionArr, $condition);
            }
            $deliveryboysUpdated++;
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setDeliveryboyData(
            $coditionData,
            ["approve_status" => ApproveStatus::STATUS_NOT_APPROVED]
        );
        foreach ($collection->getAllIds() as $deliveryboyId) {
            try {
                $currentDeliveryboy = $this->deliveryboyRepository->getById($deliveryboyId);
                $currentDeliveryboy->delete();
            } catch (\Exception $e) {
                // Do nothing if deliveryboy does not exist.
                $currentDeliveryboy;
            }
        }
        if ($deliveryboysUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were disapproved.", $deliveryboysUpdated));
        }
        return $resultRedirect->setPath("expressdelivery/deliveryboy/index");
    }
}
