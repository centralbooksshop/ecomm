<?php
namespace Magecomp\Cancelorder\Model\Config\Source\Order;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
class Autostatus implements \Magento\Framework\Option\ArrayInterface
{
    protected $_orderStatusCollection;

    protected $_options;

    public function __construct(Collection $_orderStatusCollection)
    {
        $this->_orderStatusCollection = $_orderStatusCollection;
    }
    public function toOptionArray()
    {
        if (!$this->_options) {
            foreach ($this->_orderStatusCollection->toOptionArray() as $item){
                if($item['value'] == 'pending' || $item['value'] == 'pending_payment' || $item['value'] == 'fraud' || $item['value'] == 'pending_paypal' ){
                    $options[] = ['value' => $item['value'], 'label' => $item['label']];
                }
            }
        }
        return $options;
    }
}
