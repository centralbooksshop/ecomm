<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns\Address;

use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Validator as AddressValidator;

/**
 * Class Validator
 */
class Validator extends AddressValidator
{
    /**
     * @var array
     */
    protected $required = [
        'order_id' => 'Order Id',
        // 'postcode' => 'Zip code',
        'lastname' => 'Last name',
        'street' => 'Street',
        'city' => 'City',
        // 'email' => 'Email',
        'telephone' => 'Phone Number',
        'country_id' => 'Country',
        'firstname' => 'First Name',
    ];

    /**
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    public function validate(Address $address)
    {
        $warnings = [];
        foreach ($this->required as $code => $label) {
            if (! $address->hasData($code)) {
                $warnings[] = sprintf('%s is a required field', $label);
            }
        }

        return $warnings;
    }
}
