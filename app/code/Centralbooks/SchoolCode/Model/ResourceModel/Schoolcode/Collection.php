<?php
declare(strict_types=1);

namespace Centralbooks\SchoolCode\Model\ResourceModel\Schoolcode;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'schoolcode_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\SchoolCode\Model\Schoolcode::class,
            \Centralbooks\SchoolCode\Model\ResourceModel\Schoolcode::class
        );
    }
}

