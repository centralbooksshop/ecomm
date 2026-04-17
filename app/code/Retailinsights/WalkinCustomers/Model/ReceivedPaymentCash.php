<?php

namespace Retailinsights\WalkinCustomers\Model;

/**
 * Pay In Store payment method model
 */
class ReceivedPaymentCash extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'receivedpaymentcash';
}