<?php

namespace Retailinsights\WalkinCustomers\Model;

/**
 * Pay In Store payment method model
 */
class ReceivedPaymentCard extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'receivedpaymentcard';
}