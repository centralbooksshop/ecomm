<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Returns;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Address extends \Magento\Sales\Model\ResourceModel\Order\Address
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'prrma_returns_address_resource';

    /**
     * Construct with new validator.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context                          $context
     * @param Snapshot                                                                   $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Sales\Model\ResourceModel\Attribute                               $attribute
     * @param \Magento\SalesSequence\Model\Manager                                       $sequenceManager
     * @param \Plumrocket\RMA\Model\Returns\Address\Validator                            $validator
     * @param \Magento\Sales\Model\ResourceModel\GridPool                                $gridPool
     * @param string                                                                     $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Plumrocket\RMA\Model\Returns\Address\Validator $validator,
        \Magento\Sales\Model\ResourceModel\GridPool $gridPool,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $validator,
            $gridPool,
            $connectionName
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumrocket_rma_returns_address', 'entity_id');
    }
}
