<?php
namespace Shipsy\EcommerceExtension\Model;

use Magento\Framework\Model\AbstractModel;

class Service extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Shipsy\EcommerceExtension\Model\ResourceModel\Service::class);
    }
}
