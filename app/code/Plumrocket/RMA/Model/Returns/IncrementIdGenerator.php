<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model\Returns;

use Plumrocket\RMA\Api\ReturnIncrementIdGeneratorInterface;
use Plumrocket\RMA\Model\Returns;

/**
 * @since 2.4.0
 */
class IncrementIdGenerator implements ReturnIncrementIdGeneratorInterface
{

    /**
     * If you install Custom Order Number it will generate increment and add it by plugin.
     *
     * @param int|null $storeId
     * @return string
     */
    public function generate(int $storeId = null): string
    {
        return '';
    }

    /**
     * Old logic, used when no one provide increment id using plugin on generate method.
     *
     * @param \Plumrocket\RMA\Model\Returns $return
     * @return string
     */
    public function generateIncrementIdFallback(Returns $return): string
    {
        return str_pad((string) $return->getId(), 9, '0', STR_PAD_LEFT);
    }
}
