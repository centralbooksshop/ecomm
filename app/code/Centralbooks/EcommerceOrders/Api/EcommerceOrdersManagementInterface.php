<?php
declare(strict_types=1);

namespace Centralbooks\EcommerceOrders\Api;

interface EcommerceOrdersManagementInterface
{

    /**
     * GET for EcommerceOrders api
     * @param string $pagesize
     * @return string
     */
    public function getEcommerceOrders($pagesize);
}

