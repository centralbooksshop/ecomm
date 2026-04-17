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
namespace Webkul\DeliveryBoy\Model\Rating;

use Magento\Framework\Data\OptionSourceInterface;

class Ratingstatus implements OptionSourceInterface
{
    /**
     * @param \Magento\Review\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Review\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }
    /**
     * Get Rating options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->helperData->getReviewStatusesOptionArray();
    }
}
