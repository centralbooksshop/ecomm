<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Model\ResourceModel\Items;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'fedexlabel_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Infomodus\Fedexlabel\Model\Items', 'Infomodus\Fedexlabel\Model\ResourceModel\Items');
    }
    public function addGroup($value)
    {
        $this->getSelect()->group($value);
        return $this;
    }
    public function getCreditmemoId()
    {
        $this->getShipmentId();
    }
}
