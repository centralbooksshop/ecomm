<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Addzone extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('centralbooks_zones_addzone', 'zones_id');
    }
}

