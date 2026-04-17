<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model;

use Plumrocket\RMA\Api\ReturnManagerRepositoryInterface;
use Plumrocket\RMA\Model\Config\Source\AdminUser as AdminUserSource;

/**
 * @since 2.3.0
 */
class ReturnManagerRepository implements ReturnManagerRepositoryInterface
{
    /**
     * @var \Plumrocket\RMA\Model\Config\Source\AdminUser
     */
    protected $adminUserSource;

    /**
     * @param \Plumrocket\RMA\Model\Config\Source\AdminUser $adminUserSource
     */
    public function __construct(
        AdminUserSource $adminUserSource
    ) {
        $this->adminUserSource = $adminUserSource;
    }

    /**
     * @inheritDoc
     */
    public function getList(): array
    {
        $managers = [];
        foreach ($this->adminUserSource->toArray() as $userId => $name) {
            $managers[] = [
                'id' => $userId,
                'name' => $name
            ];
        }

        return $managers;
    }

    /**
     * @param $id
     * @return bool
     */
    public function managerExists($id): bool
    {
        $managers = $this->adminUserSource->toArray();
        return array_key_exists($id, $managers);
    }
}
