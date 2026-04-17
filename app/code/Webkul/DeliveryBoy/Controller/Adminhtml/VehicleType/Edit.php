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

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\DeliveryBoy\Api\Data\VehicleTypeInterface;
use Webkul\DeliveryBoy\Controller\RegistryConstants;

class Edit extends VehicleType
{
    /**
     * Edit vehicle type.
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $vehicleTypeId = $this->initCurrentVehicleType();
        $isExistingDeliveryboy = (bool)$vehicleTypeId;
        if ($isExistingDeliveryboy) {
            try {
                $vehicleTypeData = [];
                $vehicleTypeData["expressdelivery_vehicletype"] = [];
                $vehicleType = null;
                $vehicleType = $this->vehicleTypeRepository->get($vehicleTypeId);
                $result = $vehicleType->getData();
                if (count($result)) {
                    $vehicleTypeData["expressdelivery_vehicletype"] = $result;
                    $vehicleTypeData["expressdelivery_vehicletype"][VehicleTypeInterface::ENTITY_ID]
                        = $vehicleTypeId;
                    $this->_getSession()->setVehicleTypeFormData($vehicleTypeData);
                } else {
                    $this->messageManager->addError(__("Requested vehicle type doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("expressdelivery/vehicletype/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the vehicle type."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("expressdelivery/vehicletype/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultVehicleTypeTitle($resultPage);
        $resultPage->setActiveMenu("Webkul_DeliveryBoy::vehicleType");
        if ($isExistingDeliveryboy) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $vehicleTypeId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Vehicle Type"));
        }
        return $resultPage;
    }

    /**
     * Get vehicle type Id from the request.
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

    /**
     * Prepend Title to Vehicle type page.
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultVehicleTypeTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__("Vehicle Type"));
    }
}
