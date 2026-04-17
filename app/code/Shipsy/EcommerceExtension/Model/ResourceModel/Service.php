<?php
namespace Shipsy\EcommerceExtension\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Service extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('service_types', 'entity_id');
    }
}
