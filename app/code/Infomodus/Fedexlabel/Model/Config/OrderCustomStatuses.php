<?php
namespace Infomodus\Fedexlabel\Model\Config;

class OrderCustomStatuses implements \Magento\Framework\Option\ArrayInterface
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

        $deprecatedStatuses = ['canceled' => 1, 'closed' => 1, 'complete' => 1, 'fraud' => 1, 'processing' => 1, 'pending' => 1];
        foreach ($orderStatusCollection as $orderStatus) {
            if(!isset($deprecatedStatuses[$orderStatus['status']])) {
                $status[] = [
                    'value' => $orderStatus['status'], 'label' => $orderStatus['label']
                ];
            }
        }

        return $status;
    }
}