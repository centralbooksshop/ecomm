<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbMegaMenu\Api\Data;

/**
 * UB Mega Menu Group interface.
 * @api
 */
interface GroupInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const GROUP_ID                 = 'group_id';
    const TITLE                    = 'title';
    const IDENTIFIER               = 'identifier';
    const ANIMATION_TYPE           = 'animation_type';
    const DESCRIPTION              = 'description';
    const CREATION_TIME            = 'creation_time';
    const UPDATE_TIME              = 'update_time';
    const IS_ACTIVE                = 'is_active';
    const SORT_ORDER               = 'sort_order';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get animation type
     *
     * @return string|null
     */
    public function getAnimationType();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Get sort order
     *
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setId($id);

    /**
     * Set title
     *
     * @param string $title
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setTitle($title);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set animation type
     *
     * @param string $animationType
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setAnimationType($animationType);

    /**
     * Set description
     *
     * @param string $description
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setDescription($description);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setIsActive($isActive);
}
