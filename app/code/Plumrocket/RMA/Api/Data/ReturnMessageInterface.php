<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api\Data;

/**
 * @since 2.3.0
 */
interface ReturnMessageInterface
{
    const IDENTIFIER = 'entity_id';
    const RETURN_ID = 'parent_id';
    const FROM_ID = 'from_id';
    const TYPE = 'type';
    const NAME = 'name';
    const TEXT = 'text';
    const FILES = 'files';
    const IS_SYSTEM = 'is_system';
    const IS_INTERNAL = 'is_internal';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Message from customer
     */
    const FROM_CUSTOMER = 'customer';

    /**
     * Message from manager (admin)
     */
    const FROM_MANAGER = 'manager';

    /**
     * Message from system (cron)
     */
    const FROM_SYSTEM = 'system';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return ReturnMessageInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getReturnId(): int;

    /**
     * @param int $returnId
     * @return ReturnMessageInterface
     */
    public function setReturnId(int $returnId): ReturnMessageInterface;

    /**
     * @return int
     */
    public function getFromId(): int;

    /**
     * @param int $fromId
     * @return ReturnMessageInterface
     */
    public function setFromId(int $fromId): ReturnMessageInterface;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     * @return ReturnMessageInterface
     */
    public function setType(string $type): ReturnMessageInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return ReturnMessageInterface
     */
    public function setName(string $name): ReturnMessageInterface;

    /**
     * @return string
     * @since 2.3.1
     */
    public function getContentHtml(): string;

    /**
     * @return string
     */
    public function getText(): string;

    /**
     * @param string $text
     * @return ReturnMessageInterface
     */
    public function setText(string $text): ReturnMessageInterface;

    /**
     * @return string[]
     */
    public function getPreparedFiles();

    /**
     * @param string[] $preparedFiles
     * @return ReturnMessageInterface
     */
    public function setPreparedFiles($preparedFiles): ReturnMessageInterface;

    /**
     * @return int
     */
    public function getIsSystem(): int;

    /**
     * @param int $isSystem
     * @return ReturnMessageInterface
     */
    public function setIsSystem(int $isSystem): ReturnMessageInterface;

    /**
     * @return int
     */
    public function getIsInternal(): int;

    /**
     * @param int $isInternal
     * @return ReturnMessageInterface
     */
    public function setIsInternal(int $isInternal): ReturnMessageInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return ReturnMessageInterface
     */
    public function setCreatedAt(string $createdAt): ReturnMessageInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return ReturnMessageInterface
     */
    public function setUpdatedAt(string $updatedAt): ReturnMessageInterface;
}
