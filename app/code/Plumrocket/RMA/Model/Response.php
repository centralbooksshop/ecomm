<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model;

use Plumrocket\RMA\Api\Data\ResponseTemplateInterface;

class Response extends AbstractModel implements ResponseTemplateInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Response::class);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return (string) $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return (string) $this->getData(self::MESSAGE);
    }
}
