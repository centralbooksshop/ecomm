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
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;

class Save extends VehicleType
{
    /**
     * Save vehicle type.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $vehicleTypeId = $originalRequestData["expressdelivery_vehicletype"]["entity_id"] ?? null;
        if ($originalRequestData) {
            try {
                $vehicleTypeData = $originalRequestData["expressdelivery_vehicletype"];
                $isExisting = (bool)$vehicleTypeId;
                $valueCheck = $this->collectionFactory->create()
                    ->addFieldToFilter("value", $vehicleTypeData["value"])
                    ->getFirstItem();
                $labelCheck = $this->collectionFactory->create()
                    ->addFieldToFilter("label", $vehicleTypeData["label"])
                    ->getFirstItem();
                if ((bool)$valueCheck->getId()
                    && $valueCheck->getId() != $vehicleTypeId
                ) {
                    $errors[] = __("Vehicle type with same value already exist.");
                } elseif ((bool)$labelCheck->getId()
                    && $labelCheck->getId() != $vehicleTypeId
                ) {
                    $errors[] = __("Vehicle type with same label already exist.");
                }
                $vehicleType = $this->vehicleTypeFactory->create();
                $vehicleType->setData($vehicleTypeData);
                // Save vehicleType ///////////////////////////////////////////////////////////////
                $this->vehicleTypeRepository->save($vehicleType);
                $this->_getSession()->unsVehicleTypeFormData();
                // Done Saving vehicleType, finish save action ////////////////////////////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_VEHICLE_TYPE_ID, $vehicleTypeId);

                $this->messageManager->addSuccess(__("You saved the Vehicle Type."));
                $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setVehicleTypeFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __("Something went wrong while saving the vehicle type. %1", $exception->getMessage())
                );
                $this->_getSession()->setVehicleTypeFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($vehicleTypeId) {
                $resultRedirect->setPath(
                    "expressdelivery/vehicleType/edit",
                    ["entity_id" => $vehicleTypeId, "_current"=>true]
                );
            } else {
                $resultRedirect->setPath("expressdelivery/vehicleType/new", ["_current" => true]);
            }
        } else {
            $resultRedirect->setPath("expressdelivery/vehicleType/index");
        }
        return $resultRedirect;
    }
}
