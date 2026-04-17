<?php
namespace Retailinsights\Replacelayer\Model\ResourceModel;

class Similarproductsattributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumrocket_rma_returns_replace', 'id');
    }
}
?>