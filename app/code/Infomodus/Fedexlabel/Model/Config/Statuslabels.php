<?php
namespace Infomodus\Fedexlabel\Model\Config;
class Statuslabels implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Success')],
            ['value' => '1', 'label' => __('Error')],
        ];
    }
    public function getStatus()
    {
        return [
            'success' => __('Success'),
            'error' => __('Error'),
            'notcreated' => __('Not created'),
            'pending' => __('FedEx Pending'),
        ];
    }
}