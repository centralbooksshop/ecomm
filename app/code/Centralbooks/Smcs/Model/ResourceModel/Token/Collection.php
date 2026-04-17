<?php
namespace Centralbooks\Smcs\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Centralbooks\Smcs\Model\Token::class,
            \Centralbooks\Smcs\Model\ResourceModel\Token::class
        );
    }
}