<?php
namespace Magecomp\Cancelorder\Model\Config\Source\Order;
class Status implements \Magento\Framework\Option\ArrayInterface
{
    const UNDEFINED_OPTION_LABEL = '-- Please Select --';

    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_COMPLETE,
        \Magento\Sales\Model\Order::STATE_CLOSED,
        \Magento\Sales\Model\Order::STATE_CANCELED,
        \Magento\Sales\Model\Order::STATE_HOLDED,
    ];

    protected $_orderConfig;
    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig)
    {
        $this->_orderConfig = $orderConfig;
    }
    public function toOptionArray()
    {
        $statuses = $this->_stateStatuses
            ? $this->_orderConfig->getStateStatuses($this->_stateStatuses)
            : $this->_orderConfig->getStatuses();

        foreach ($statuses as $code => $label) {
            if($code=='pending' || $code=='processing'){
                $options[] = ['value' => $code, 'label' => $label];
           }
        }
        return $options;
    }
}
