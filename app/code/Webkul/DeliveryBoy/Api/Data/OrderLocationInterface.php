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

interface OrderLocationInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ID = "entity_id";
    public const ORDER_ID = "order_id";
    public const LATITUDE = "latitude";
    public const LONGITUDE = "longitude";

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     * @return self
     */
    public function setId($id);

    /**
     * Get Order ID
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set ORder Id
     *
     * @param string $deliveryboyOrdeId
     * @return self
     */
    public function setOrderId($deliveryboyOrdeId);

    /**
     * Get Latitude
     *
     * @return string|null
     */
    public function getLatitude();

    /**
     * Set Latitude
     *
     * @param string $latitude
     * @return self
     */
    public function setLatitude($latitude);

    /**
     * Get Longitude
     *
     * @return float|null
     */
    public function getLongitude();

    /**
     * Get Longitude
     *
     * @param float $longitude
     * @return self
     */
    public function setLongitude($longitude);
}
