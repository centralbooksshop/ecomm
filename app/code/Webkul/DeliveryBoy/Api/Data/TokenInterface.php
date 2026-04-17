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

interface TokenInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ID = "id";
    public const OS = "os";
    public const IS_ADMIN = "is_admin";
    public const TOKEN = "token";
    public const DELIVERYBOY_ID = "deliveryboy_id";

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
     * Get Os
     *
     * @return string|null
     */
    public function getOs();

    /**
     * Set Os
     *
     * @param string $os
     * @return self
     */
    public function setOs($os);

    /**
     * Get Token
     *
     * @return string|null
     */
    public function getToken();

    /**
     * Set Token
     *
     * @param string $token
     * @return self
     */
    public function setToken($token);

    /**
     * Get Deliveryboy Id.
     *
     * @return int
     */
    public function getDeliveryboyId();

    /**
     * Set Deliveyrboy Id.
     *
     * @param int $deliveryboyId
     * @return self
     */
    public function setDeliveryboyId($deliveryboyId);
}
