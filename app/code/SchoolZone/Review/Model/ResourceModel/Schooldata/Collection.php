<?php
namespace SchoolZone\Review\Model\ResourceModel\Schooldata;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SchoolZone\Review\Model\Schooldata as Model;
use SchoolZone\Review\Model\ResourceModel\Schooldata as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}

