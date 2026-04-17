<?php
namespace SchoolZone\Review\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Schooldata extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('schoolzone_review', 'entity_id'); 
    }
}

