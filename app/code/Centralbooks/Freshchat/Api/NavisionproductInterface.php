<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */



namespace Centralbooks\Freshchat\Api;

/**
 * @api
 */
interface NavisionproductInterface {

    /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param mixed $data
     * @@return string[]
     */
    public function getNaviosionproduct($data);

    /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param string $mobile
     * @@return string[]
     */
    public function getCustomerorder($mobile);
    /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param int $id
     * @@return string[]
     */
    public function Creatermabot($id);
     /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param int $id
     * @@return string[]
     */
    public function getRMAinformation($id);
     /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param string $id
     * @@return string[]
     */
    public function getIteminformation($id);
     /**
     * Return the sum of the two numbers.
     *
     * @@return string[]
     */
    public function Createrma();
}
