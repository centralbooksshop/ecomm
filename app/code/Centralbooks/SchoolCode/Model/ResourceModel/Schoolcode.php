<?php
declare(strict_types=1);

namespace Centralbooks\SchoolCode\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Schoolcode extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('centralbooks_schoolcode_schoolcode', 'schoolcode_id');
    }
}

