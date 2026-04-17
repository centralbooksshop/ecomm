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

class Validate extends VehicleType
{
    /**
     * Validate Vehicle type.
     *
     * @param \Magento\Framework\DataObject $response
     * @return \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface
     */
    protected function _validateVehicleType($response)
    {
        $vehicleType = null;
        $errors = [];
        try {
            $vehicleType = $this->vehicleTypeFactory->create();
            $data = $this->getRequest()->getParams();
            $dataResult = $data["expressdelivery_vehicletype"];
            $vehicleTypeData = $dataResult;
            $errors = [];
            
            if (!isset($dataResult["value"])) {
                $errors[] = __("Vehicle type value is a required field.");
            }
            if (!isset($dataResult["label"])) {
                $errors[] = __("Vehicle type label is a required field.");
            }

            $vehicleTypeId = $dataResult["entity_id"] ?? 0;
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
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $exceptionMsg = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            foreach ($exceptionMsg as $error) {
                $errors[] = $error->getText();
            }
        }
        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }
        return $vehicleType;
    }

    /**
     * Return Validation result in Json.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $deliveryboy = $this->_validateVehicleType($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
