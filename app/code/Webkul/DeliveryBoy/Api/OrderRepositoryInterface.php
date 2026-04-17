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
namespace Webkul\DeliveryBoy\Api;

use Webkul\DeliveryBoy\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface OrderRepositoryInterface
{
    /**
     * Get Order by Id.
     *
     * @param int $id
     * @return OrderInterface
     */
    public function getById($id);

    /**
     * Delete Order BY Id.
     *
     * @param int $id
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($id);

    /**
     * Save Order By Id.
     *
     * @param OrderInterface $deliveryboy
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(OrderInterface $deliveryboy);

    /**
     * Delete Deliveryboy By Id.
     *
     * @param OrderInterface $deliveryboy
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(OrderInterface $deliveryboy);

    /**
     * Get Order List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
