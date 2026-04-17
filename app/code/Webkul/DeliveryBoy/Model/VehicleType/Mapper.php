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
namespace Webkul\DeliveryBoy\Model\VehicleType;

use Magento\Framework\Api\ExtensibleDataObjectConverter;

class Mapper
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Convert Object to array.
     *
     * @param  \Webkul\DeliveryBoy\Api\Data\VehicleTypeInterface $deliveryboy
     * @return array
     */
    public function toFlatArray(\Webkul\DeliveryBoy\Api\Data\VehicleTypeInterface $deliveryboy)
    {
        $nestedArray = $this->extensibleDataObjectConverter->toNestedArray(
            $deliveryboy,
            [],
            \Webkul\DeliveryBoy\Api\Data\VehicleTypeInterface::class
        );
        return \Magento\Framework\Convert\ConvertArray::toFlatArray($nestedArray);
    }
}
