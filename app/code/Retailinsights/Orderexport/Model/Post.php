<?php
namespace Retailinsights\Orderexport\Model;

class Post extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\Orderexport\Model\ResourceModel\Post');
    }
}
?>