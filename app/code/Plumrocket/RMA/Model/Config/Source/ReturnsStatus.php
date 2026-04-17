<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Config\Source;

class ReturnsStatus extends AbstractSource
{
    /**
     * After create by customer, no one is authorized
     */
    const STATUS_NEW                = 'new';

    /**
     * (Only for return) At least one item is rejected and no one is authorized
     */
    const STATUS_REJECTED_PART      = 'rejected_part';

    /**
     * All items are declined or are not authorized
     */
    const STATUS_REJECTED           = 'rejected';

    /**
     * At least one item is authorized
     */
    const STATUS_AUTHORIZED_PART    = 'authorized_part';

    /**
     * All items are authorized
     */
    const STATUS_AUTHORIZED         = 'authorized';

    /**
     * At least one item is received
     */
    const STATUS_RECEIVED_PART      = 'received_part';

    /**
     * All items are received
     */
    const STATUS_RECEIVED           = 'received';

    /**
     * At least one item is approved
     */
    const STATUS_APPROVED_PART      = 'approved_part';

    /**
     * At least one item is approved, all items are finished
     */
    const STATUS_PROCESSED_CLOSED   = 'processed_closed';

    /**
     * Return was cancelled
     *
     * @deprecated since 2.2.3 - improper name
     * @see ReturnsStatus::STATUS_CANCELLED instead
     */
    const STATUS_CLOSED             = self::STATUS_CANCELLED;

    /**
     * Return was cancelled
     */
    const STATUS_CANCELLED          = 'closed';
    
	/**
     * Return was handedover
     */
    const STATUS_Handedover =      'handedover';

	/**
     * Return was operations
     */
    const STATUS_Operations =      'operations';

	/**
     * Return was nostock
     */
    const STATUS_Nostock =       'nostock';

	/**
     * Return was notresponding
     */
    const STATUS_NOTRESPONDING =       'notresponding';

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            self::STATUS_NEW                => __('Pending'),
            //self::STATUS_REJECTED_PART      => __('Partially Rejected'),
            self::STATUS_AUTHORIZED_PART    => __('Partially Approved'),
            self::STATUS_AUTHORIZED         => __('Approved'),
            //self::STATUS_RECEIVED_PART      => __('Partially Received'),
            //self::STATUS_APPROVED_PART      => __('Partially Resolved'),
            //self::STATUS_REJECTED           => __('Rejected'),
            self::STATUS_Operations      => __('Pending with Operations'),
            self::STATUS_Nostock          => __('No Stock'),
		    self::STATUS_Handedover      => __('Handed over to outward'),
			self::STATUS_RECEIVED           => __('In Transit'),
			self::STATUS_PROCESSED_CLOSED   => __('Resolved'),
            self::STATUS_CANCELLED          => __('Cancelled'),
			self::STATUS_NOTRESPONDING          => __('Customer Not Responding'),
        ];
    }

    /**
     * Retrieve final statuses
     *
     * @return array
     */
    public function getFinalStatuses()
    {
        return [
            self::STATUS_PROCESSED_CLOSED   => __('Resolved'),
            self::STATUS_CANCELLED          => __('Cancelled'),
            //self::STATUS_REJECTED           => __('Rejected'),
        ];
    }
}
