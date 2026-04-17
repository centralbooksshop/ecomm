<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Config\Source;

use Plumrocket\Base\Api\ExtensionStatusInterface;
use Plumrocket\Base\Api\GetExtensionStatusInterface;
use Plumrocket\RMA\Helper\Data;

class Position extends AbstractSource
{
    // const CATEGORY              = 'category';
    const PRODUCT               = 'product';
    const SHOPPING_CART         = 'shopping_cart';
    const CHECKOUT              = 'checkout';
    const PM_ORDER_SUCCESS      = 'pm_order_success';
    const CUSTOMER_ORDER        = 'customer_order';
    const ORDER_CONFIRMATION    = 'order_confirmation';
    const INVOICE               = 'invoice';
    const SHIPMENT              = 'shipment';
    const ADMINPANEL_ORDER      = 'adminpanel_order';

    /**
     * @var \Plumrocket\Base\Api\GetExtensionStatusInterface
     */
    private $getExtensionStatus;

    /**
     * @param \Plumrocket\RMA\Helper\Data                      $dataHelper
     * @param \Plumrocket\Base\Api\GetExtensionStatusInterface $getExtensionStatus
     */
    public function __construct(
        Data $dataHelper,
        GetExtensionStatusInterface $getExtensionStatus
    ) {
        parent::__construct($dataHelper);
        $this->getExtensionStatus = $getExtensionStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $cspStatus = $this->getExtensionStatus->execute('Checkoutspage');
            $checkoutspageEnabled = $cspStatus === ExtensionStatusInterface::ENABLED;

            $this->options = [
                [
                    'label' => __('None'),
                    'value' => 'none',
                ],
                [
                    'label' => __('Frontend Pages'),
                    'value' => [
                        /*[
                            'value' => self::CATEGORY,
                            'label' => __('Category Page')
                        ],*/
                        [
                            'value' => self::PRODUCT,
                            'label' => __('Product Page')
                        ],
                        [
                            'value' => self::SHOPPING_CART,
                            'label' => __('Shopping Cart Page')
                        ],
                        [
                            'value' => self::CHECKOUT,
                            'label' => __('Checkout Page')
                        ],
                        [
                            'value' => self::PM_ORDER_SUCCESS,
                            'label' => __(
                                'Plumrocket Checkout Success Page' . (!$checkoutspageEnabled ? ' (Not installed)' : '')
                            ),
                            'style' => (!$checkoutspageEnabled ? 'color: #999;' : '')],
                        [
                            'value' => self::CUSTOMER_ORDER,
                            'label' => __('Customer Account > Order Page')
                        ],
                    ]
                ],
                [
                    'label' => __('Emails'),
                    'value' => [
                        [
                            'value' => self::ORDER_CONFIRMATION,
                            'label' => __('Order Confirmation')
                        ],
                        [
                            'value' => self::INVOICE,
                            'label' => __('Invoice')
                        ],
                        [
                            'value' => self::SHIPMENT,
                            'label' => __('Shipment')
                        ],
                    ]
                ],
                [
                    'label' => __('Admin Panel'),
                    'value' => [
                        [
                            'value' => self::ADMINPANEL_ORDER,
                            'label' => __('Order Page')
                        ],
                    ]
                ],
            ];
        }

        return $this->options;
    }
}
