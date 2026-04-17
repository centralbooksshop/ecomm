<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Plumrocket\RMA\Helper\Data;

class Info extends Template
{
    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Context       $context
     * @param Renderer      $addressRenderer
     * @param Session       $session
     * @param array         $data
     */
    public function __construct(
        Context $context,
        Renderer $addressRenderer,
        Session $session,
        array $data = []
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * Get order view URL.
     *
     * @param int|\Magento\Sales\Model\Order $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        if (is_object($orderId)) {
            $orderId = $orderId->getId();
        }

        if ($this->session->isLoggedIn()) {
            return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
        } else {
            return $this->getUrl('sales/guest/view', ['order_id' => $orderId]);
        }
    }

    /**
     * Get link to edit order address page
     *
     * @param Address $address
     * @param string $label
     * @return string
     */
    public function getAddressEditLink($address, $label = '')
    {
        if (empty($label)) {
            $label = (string) __('Edit');
        }

        $url = $this->getUrl(Data::SECTION_ID . '/returns/address', [
            'order_id' => $this->getOrder()->getId()
        ]);
        return '<a id="address-edit" href="' . $url . '">' . $label . '</a>';
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return string
     */
    public function getFormattedAddress(Address $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * Get store address
     *
     * @return string
     */
    public function getStoreAddress()
    {
        $address = $this->configHelper->getStoreAddress();
        if ($address) {
            $address = nl2br($address);
        }

        return $address;
    }

    /**
     * Get entity address
     *
     * @return Address|null
     */
    public function getAddress()
    {
        if ($this->getEntity()->isVirtual()) {
            return;
        }

        $address = $this->getEntity()->getAddress();
        if (! $address->getId()) {
            /**
             * For create page
             */
            $order = $this->getOrder();
            if ($order && $order->getId()) {
                $unassignedaddress = $address->getUnassigned($order->getId());
                if ($this->isNewEntity() && $unassignedaddress) {
                    $address = $unassignedaddress;
                } else {
                    $address = $order->getShippingAddress();
                }
            }
        }

        return $address && $address->getId() ? $address : null;
    }

 public function getProductName()
 {
    $order = $this->getOrder();
    if ($order && $order->getId()) {
	$items = $order->getItems();
	$productName = "";
           foreach ($items as $item) {
               if ($item->getProductType() == 'bundle') {
                    $productName = $item->getName(); 
                }
            }
	 return $productName;
     }
 }

}
