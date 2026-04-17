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
namespace Webkul\DeliveryBoy\Model\Deliveryboy\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\DeliveryBoy\Model\Deliveryboy;

class ApproveStatus implements OptionSourceInterface
{
    public const STATUS_APPROVED = 1;
    public const STATUS_NOT_APPROVED = 0;

    /**
     * Get Options for Deliveryboy approval.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            "label" => __("Approved"),
            "value" => self::STATUS_APPROVED,
        ];
        $options[] = [
            "value" => self::STATUS_NOT_APPROVED,
            "label" => __("Disapproved"),
        ];

        return $options;
    }
}
