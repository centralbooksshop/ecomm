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

interface VehicleTypeInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = "entity_id";
    public const VALUE = "value";
    public const LABEL = "label";
    public const CREATED_AT = "created_at";
    public const UPDATED_AT = "updated_at";

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id.
     *
     * @param int $id
     * @return self
     */
    public function setId($id);

    /**
     * Get Value
     *
     * @return string|null
     */
    public function getValue();

    /**
     * Set Value
     *
     * @param string $value
     * @return self
     */
    public function setValue($value);

    /**
     * Get Label
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * Set Label.
     *
     * @param string $label
     * @return self
     */
    public function setLabel($label);
}
