<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transactions extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('centralbooks_transactions', 'id');
    }
	
    /**
     * @inheritDoc
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = "vid";
        }
        return parent::load($object, $value, $field);
    }

}



