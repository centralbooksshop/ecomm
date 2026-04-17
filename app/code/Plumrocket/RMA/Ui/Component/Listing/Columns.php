<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Ui\Component\Listing;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => $item) {
                $dataSource['data']['items'][$key]['class'] = $this->getReplyStatusClass($item['reply_at'], $item);
                $dataSource['data']['items'][$key]['reply_name'] = $this->decorateReplyName($item['reply_name'], $item);
            }
        }

        return $dataSource;
    }

    /**
     * Decorate cells of row which is with reply
     *
     * @param string $value
     * @param array  $row
     * @return string
     */
    public function getReplyStatusClass($value, $row)
    {
        $readAt = ! empty($row['read_mark_at']) ? $row['read_mark_at'] : null;
        $replyAt = ! empty($row['reply_at']) ? $row['reply_at'] : null;

        if (null === $replyAt) {
            return '';
        }

        if (null === $readAt
            || ($readAt && strtotime($readAt) < strtotime($replyAt))
        ) {
            return 'prrma-replied';
        }

        return '';
    }

    /**
     * Decorate cell of last reply
     *
     * @param string $value
     * @param array  $row
     * @return string
     */
    public function decorateReplyName($value, $row)
    {
        return $value ? __(' by %1', $value) : '';
    }
}
