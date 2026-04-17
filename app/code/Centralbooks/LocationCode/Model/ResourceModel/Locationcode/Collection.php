<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Model\ResourceModel\Locationcode;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'locationcode_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\LocationCode\Model\Locationcode::class,
            \Centralbooks\LocationCode\Model\ResourceModel\Locationcode::class
        );
    }
}

