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

use Webkul\DeliveryBoy\Controller\RegistryConstants;

class Delete extends VehicleType
{
    /**
     * Delete Vehicle type.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Vehicle type could not be deleted."));
            return $resultRedirect->setPath("expressdelivery/vehicletype/index");
        }
        $vehicleTypeId = $this->initCurrentVehicleType();
        if (!empty($vehicleTypeId)) {
            try {
                $this->vehicleTypeRepository->deleteById($vehicleTypeId);
                $this->messageManager->addSuccess(__("Vehicle type has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("expressdelivery/vehicletype/index");
    }

    /**
     * Get Vehicle type id from the request.
     *
     * @return int|null
     */
    protected function initCurrentVehicleType()
    {
        $vehicleTypeId = (int)$this->getRequest()->getParam("entity_id");
        if ($vehicleTypeId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_VEHICLE_TYPE_ID, $vehicleTypeId);
        }
        return $vehicleTypeId;
    }
}
