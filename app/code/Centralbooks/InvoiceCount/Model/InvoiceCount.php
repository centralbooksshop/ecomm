<?php
namespace Centralbooks\InvoiceCount\Model;

use Magento\Framework\Model\AbstractModel;

class InvoiceCount extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Centralbooks\InvoiceCount\Model\ResourceModel\InvoiceCount::class);
    }
}
