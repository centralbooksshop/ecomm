<?php
declare(strict_types=1);

namespace Centralbooks\ExtraCheckoutAddressFields\Plugin\Magento\Checkout\Model;

use Centralbooks\ExtraCheckoutAddressFields\Helper\Data;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

class PaymentInformationManagement
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * PaymentInformationManagement constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $address
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
       \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $address = null
    ) {
        if (is_null($address))
            return;
		$extAttributes = $address->getExtensionAttributes();
		//echo '<pre>';print_r($extAttributes);die;
        if (!empty($extAttributes)) {
            $this->helper->transportFieldsFromExtensionAttributesToObject(
                $extAttributes,
                $address,
                'extra_checkout_billing_address_fields'
            );
        }
    }
}
