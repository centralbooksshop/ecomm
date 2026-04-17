<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Model\ResourceModel\Schoolhub;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'schoolhub_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\SchoolHub\Model\Schoolhub::class,
            \Centralbooks\SchoolHub\Model\ResourceModel\Schoolhub::class
        );
    }
}

