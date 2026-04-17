<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Api\Data;

/**
 * UbContentSlider slide interface.
 * @api
 */
interface SlideInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SLIDE_ID                 = 'slide_id';
    const IDENTIFIER               = 'identifier';
    const TITLE                    = 'title';
    const DESCRIPTION              = 'description';
    const CREATION_TIME            = 'creation_time';
    const UPDATE_TIME              = 'update_time';
    const SORT_ORDER               = 'sort_order';
    const IS_ACTIVE                = 'is_active';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

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
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setId($id);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set title
     *
     * @param string $title
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setTitle($title);

    /**
     * Set description
     *
     * @param string $description
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setDescription($description);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Ubertheme\UbContentSlider\Api\Data\SlideInterface
     */
    public function setIsActive($isActive);
}
