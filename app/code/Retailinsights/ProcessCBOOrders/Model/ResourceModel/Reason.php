<?php
namespace Retailinsights\ProcessCBOOrders\Model\ResourceModel;

class Reason extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init("cbo_notdelivered_reason", "id");
    }

    /**
     * Load Model.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return self
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = "id";
        }
        return parent::load($object, $value, $field);
    }
}
