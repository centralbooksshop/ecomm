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
namespace Webkul\DeliveryBoy\Api\Data;

interface OrderTransactionSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Webkul\DeliveryBoy\Api\Data\OrderTransactionInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Webkul\DeliveryBoy\Api\Data\OrderTransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
