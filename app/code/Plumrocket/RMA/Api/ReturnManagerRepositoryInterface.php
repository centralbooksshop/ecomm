<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api;

/**
 * @since 2.3.0
 */
interface ReturnManagerRepositoryInterface
{
    /**
     * Retrieve array of managers
     *
     * Format:
     * [[
     *  'id' => id,
     *  'name' => 'name'
     * ]]
     *
     * @return string[][]
     */
    public function getList(): array;
}
