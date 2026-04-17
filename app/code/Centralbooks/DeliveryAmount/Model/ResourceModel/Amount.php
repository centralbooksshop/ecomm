<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Amount extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('centralbooks_zones_amount', 'zones_id');
    }

    /**
     * @inheritDoc
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = "zone_token";
        }
        return parent::load($object, $value, $field);
    }
}


