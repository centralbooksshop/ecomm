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
namespace Webkul\DeliveryBoy\Model\VehicleType\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\DeliveryBoy\Model\ResourceModel\VehicleType\CollectionFactory;

class Option implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $vehicleTypeCF;

    /**
     * @param CollectionFactory $vehicleTypeCF
     */
    public function __construct(CollectionFactory $vehicleTypeCF)
    {
        $this->vehicleTypeCF = $vehicleTypeCF;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $availableOptions = $this->vehicleTypeCF->create()->getItems();
        $options = [];
        foreach ($availableOptions as $item) {
            $options[] = [
                "label" => __($item->getLabel()),
                "value" => $item->getValue(),
            ];
        }
        return $options;
    }
}
