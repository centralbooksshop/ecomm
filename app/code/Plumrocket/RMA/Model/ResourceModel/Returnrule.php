<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Returnrule extends AbstractDb
{
    /**
     * Required property
     * @var integer
     */
    protected $_entityTypeId = 5;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumrocket_rma_return_rule', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return $this->_entityTypeId;
    }
}
