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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Edit\VehicleType;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * GEt Button Configuration.
     *
     * @return array
     */
    public function getButtonData()
    {
        $vehicleTypeId = $this->getVehicleTypeId();
        $data = [];
        $escapedMessage = __("Are you sure want to delete this record?");
        $url = $this->getDeleteUrl();
        $idFieldName = 'entity_id';
        $fieldId = $this->getVehicleTypeId();
        if ($vehicleTypeId) {
            $data = [
                "id" => "vehicletype-edit-delete-button",
                "label" => __("Delete Vehicle Type"),
                "class" => "delete",
                'on_click' => "deleteConfirm('{$escapedMessage}', '{$url}', {data:{{$idFieldName}:{$fieldId}}})",
                "sort_order" => 20,
            ];
        }
        return $data;
    }

    /**
     * Get Delete Url.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("*/*/delete", ["entity_id" => $this->getVehicleTypeId()]);
    }
}
