<?php
namespace Lof\PincodeChecker\Model\Pincodechecker\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Lof\PincodeChecker\Model\Pincodechecker;

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
            "label" => __("Yes"),
            "value" => self::STATUS_APPROVED,
        ];
        $options[] = [
            "value" => self::STATUS_NOT_APPROVED,
            "label" => __("No"),
        ];

        return $options;
    }
}
