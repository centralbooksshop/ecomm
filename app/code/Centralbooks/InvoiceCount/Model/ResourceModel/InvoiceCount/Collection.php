<?php
namespace Centralbooks\InvoiceCount\Model\ResourceModel\InvoiceCount;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\InvoiceCount\Model\InvoiceCount::class,
            \Centralbooks\InvoiceCount\Model\ResourceModel\InvoiceCount::class
        );
    }
}
