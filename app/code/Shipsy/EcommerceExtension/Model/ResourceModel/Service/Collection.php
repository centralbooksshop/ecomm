<?php
namespace Shipsy\EcommerceExtension\Model\ResourceModel\Service;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Shipsy\EcommerceExtension\Model\Service::class,
            \Shipsy\EcommerceExtension\Model\ResourceModel\Service::class
        );
    }
}
