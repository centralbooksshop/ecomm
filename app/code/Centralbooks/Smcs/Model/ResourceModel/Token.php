<?php
namespace Centralbooks\Smcs\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Token extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('centralbooks_smcs_token', 'entity_id');
    }
}