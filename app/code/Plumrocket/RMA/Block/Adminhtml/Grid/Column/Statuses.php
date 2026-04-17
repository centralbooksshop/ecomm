<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Grid\Column;

class Statuses extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Add decorator to column
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorator'];
    }

    /**
     * Decorate column values
     *
     * @param string $value
     * @param \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorator($value, $row, $column, $isExport)
    {
        if ($row->getStatus()) {
            $cell = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span>' . $value . '</span></span>';
        }
        return $cell;
    }
}
