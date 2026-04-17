<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Plumrocket\RMA\Api\ReturnIncrementIdGeneratorInterface;

class Returns extends AbstractDb implements EntityResourceInterface
{
    const MAIN_TABLE_NAME = 'plumrocket_rma_returns';

    /**
     * Required property
     * @var integer
     */
    protected $_entityTypeId = false;

    /**
     * @var \Plumrocket\RMA\Api\ReturnIncrementIdGeneratorInterface
     */
    private $generateReturnIncrementId;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context       $context
     * @param \Plumrocket\RMA\Api\ReturnIncrementIdGeneratorInterface $generateReturnIncrementId
     * @param null                                                    $connectionName
     */
    public function __construct(
        Context $context,
        ReturnIncrementIdGeneratorInterface $generateReturnIncrementId,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->generateReturnIncrementId = $generateReturnIncrementId;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE_NAME, 'entity_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return $this->_entityTypeId;
    }

    /**
     * Perform actions before object save, calculate next sequence value for increment Id
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Plumrocket\RMA\Model\Returns $object */
        if ($object->getEntityId() == null && $object->getIncrementId() == null) {
            $store = $object->getStore();
            $storeId = $store->getId();
            if ($storeId === null) {
                $storeId = $store->getGroup()->getDefaultStoreId();
            }
            $object->setIncrementId(
                $this->generateReturnIncrementId->generate((int) $storeId)
            );
        }
        parent::_beforeSave($object);
        return $this;
    }
}
