<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Response extends AbstractDb implements EntityResourceInterface
{
    /**
     * Required property
     * @var integer
     */
    protected $_entityTypeId = 4;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumrocket_rma_response_template', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return $this->_entityTypeId;
    }
}
