<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Ui\Component\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var ReturnsStatus
     */
    protected $status;

    /**
     * @param ReturnsStatus $status
     */
    public function __construct(ReturnsStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->status->toOptionArray();
    }
}
