<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Schoolhub extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('centralbooks_schoolhub_schoolhub', 'schoolhub_id');
    }
}

