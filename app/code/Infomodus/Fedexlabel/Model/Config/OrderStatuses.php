<?php
namespace Infomodus\Fedexlabel\Model\Config;

class OrderStatuses implements \Magento\Framework\Option\ArrayInterface
{
    protected $statusesOrder;

    public function __construct(\Magento\Sales\Model\Order\Status $statusesOrder)
    {
        $this->statusesOrder = $statusesOrder;
    }

    public function toOptionArray($isMultiSelect = false)
    {
        $orderStatusCollection = $this->statusesOrder->getResourceCollection()->getData();
        $status = [
           ['value' => "", 'label' => '--Please Select--']
        ];
        foreach ($orderStatusCollection as $orderStatus) {
                $status[] = [
                    'value' => $orderStatus['status'], 'label' => $orderStatus['label']
                ];
        }

        return $status;
    }
}