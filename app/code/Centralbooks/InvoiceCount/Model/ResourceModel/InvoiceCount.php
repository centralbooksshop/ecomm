<?php
namespace Centralbooks\InvoiceCount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InvoiceCount extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('invoice_download_count', 'id'); // table name and primary key
    }
}
