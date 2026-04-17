<?php
namespace Infomodus\Fedexlabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class ReferenceTypeRefund implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('CUSTOMER_REFERENCE'), 'value' => 'CUSTOMER_REFERENCE'],
            ['label' => __('BILL_OF_LADING'), 'value' => 'BILL_OF_LADING'],
            ['label' => __('DEPARTMENT_NUMBER'), 'value' => 'DEPARTMENT_NUMBER'],
            ['label' => __('ELECTRONIC_PRODUCT_CODE'), 'value' => 'ELECTRONIC_PRODUCT_CODE'],
            ['label' => __('INTRACOUNTRY_REGULATORY_REFERENCE'), 'value' => 'INTRACOUNTRY_REGULATORY_REFERENCE'],
            ['label' => __('INVOICE_NUMBER'), 'value' => 'INVOICE_NUMBER'],
            ['label' => __('P_O_NUMBER'), 'value' => 'P_O_NUMBER'],
            ['label' => __('RMA_ASSOCIATION'), 'value' => 'RMA_ASSOCIATION'],
            ['label' => __('SHIPMENT_INTEGRITY'), 'value' => 'SHIPMENT_INTEGRITY'],
            ['label' => __('STORE_NUMBER'), 'value' => 'STORE_NUMBER'],
        ];
        return $c;
    }
}