<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Api;

/**
 * @since 2.4.0
 */
interface ReturnIncrementIdGeneratorInterface
{

    /**
     * Point for modification.
     *
     * @param int|null $storeId
     * @return string
     */
    public function generate(int $storeId = null): string;
}
