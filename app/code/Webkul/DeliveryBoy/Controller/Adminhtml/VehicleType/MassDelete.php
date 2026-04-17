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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\VehicleType;

class MassDelete extends VehicleType
{
    /**
     * Mass delete vehicle types.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection     = $this->filter->getCollection($this->collectionFactory->create());
        $vehicleTypesDeleted = 0;
        foreach ($collection->getAllIds() as $vehicleTypeId) {
            if (!empty($vehicleTypeId)) {
                try {
                    $this->vehicleTypeRepository->deleteById($vehicleTypeId);
                    $vehicleTypesDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($vehicleTypesDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $vehicleTypesDeleted));
        }
        return $resultRedirect->setPath("expressdelivery/vehicletype/index");
    }
}
