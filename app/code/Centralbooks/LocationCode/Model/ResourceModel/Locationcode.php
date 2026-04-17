<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Locationcode extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('centralbooks_locationcode_locationcode', 'locationcode_id');
    }
}

