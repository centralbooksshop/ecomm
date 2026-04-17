<?php
namespace SchoolZone\Review\Model;

use Magento\Framework\Model\AbstractModel;

class Schooldata extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\SchoolZone\Review\Model\ResourceModel\Schooldata::class);
    }
}

