<?php
namespace Centralbooks\InvoiceCount\Api;

interface InvoiceCountManagementInterface
{
    /**
     * Update invoice download count for multiple orders
     *
     * @param mixed $data
     * @return string
     */
    public function updateInvoiceCount($data);

}

