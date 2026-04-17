<?php
namespace Centralbooks\Smcs\Model;

use Magento\Framework\Model\AbstractModel;

class Token extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Centralbooks\Smcs\Model\ResourceModel\Token::class);
    }
}