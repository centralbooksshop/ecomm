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
namespace Webkul\DeliveryBoy\Model\OrderTransaction\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status is used tp get the order available status
 */
class Status implements OptionSourceInterface
{
    public const IS_CLOSED_YES = 1;
    public const IS_CLOSED_NO = 0;

    /**
     * Get Options hash.
     *
     * @return array
     */
    public function getOptionsWithLabel()
    {
        return [
            self::IS_CLOSED_YES => 'CLOSED',
            self::IS_CLOSED_NO => 'NOT CLOSED',
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->getOptionsWithLabel();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
