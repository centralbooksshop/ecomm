<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Config\Source;

class ReasonId extends AbstractSource
{
    const STATUS_ENABLED = 1;
    const STATUS_ENABLED2 = 2;
    const STATUS_ENABLED3 = 3;
    const STATUS_ENABLED4 = 4;
    const STATUS_ENABLED5 = 5;
    const STATUS_ENABLED6 = 6;
    const STATUS_ENABLED7 = 7;
    const STATUS_DISABLED = 0;

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            self::STATUS_ENABLED    => __('Enabled'),
            self::STATUS_ENABLED2    => __('Enabled2'),
            self::STATUS_ENABLED3   => __('Enabled3'),
            self::STATUS_ENABLED4    => __('Enabled4'),
            self::STATUS_ENABLED5    => __('Enabled5'),
            self::STATUS_ENABLED6    => __('Enabled6'),
            self::STATUS_ENABLED7    => __('Enabled7'),
            self::STATUS_DISABLED   => __('Disabled'),
        ];
    }
}
