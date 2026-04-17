<?php

namespace Retailinsights\WalkinCustomers\Model\Source;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = $this->toOptionArray();
        $arrayNew = [];
        foreach($array as $row) {
            $arrayNew[$row['value']] = $row['label'];
        }
        return $arrayNew;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();

        $options[] = array(
            'value' => 'processing',
            'label' => __('Authorized'),

        );
        $options[] = array(
            'value' => 'pending',
            'label' => __('Bank Transfer'),

        );
        $options[] = array(
            'value' => 'cod_payment',
            'label' => __('Cash On Delivery'),

        );
        return $options;
    }
}