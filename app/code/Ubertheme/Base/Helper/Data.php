<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\Base\Helper;

use \Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Data
 * @package Ubertheme\Checkout\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        ObjectManagerInterface $objectmanager
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_objectManager = $objectmanager;

        parent::__construct($context);
    }

    public function getAjaxCompareOptions() {
        $options = [
            'ajaxCompareUrl' => $this->_getUrl('catalog/product_compare/add/')
        ];

        return $this->_jsonEncoder->encode($options);
    }

    public function getAjaxWishlistOptions() {
        $customerId = $this->getCustomerId();
        $options = [
            'ajaxWishlistUrl' => $this->_getUrl('wishlist/index/add/'),
            'loginUrl' => $this->_getUrl('customer/account/login'),
            'customerId' => $customerId
        ];

        return $this->_jsonEncoder->encode($options);
    }

    public function getAjaxCartOptions() {
        $options = [
            'ajaxCartUrl' => $this->_getUrl('checkout/cart/add/')
        ];

        return $this->_jsonEncoder->encode($options);
    }

    public function getCustomerId() {
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->create("Magento\Customer\Model\Session");
        return $customerSession->getCustomer()->getId();
    }

    public function getLatestVersion($moduleName) {
        $version = '';
        $modules = self::getUBModules();
        if (isset($modules[$moduleName])) {
            $version = $modules[$moduleName];
        }

        return $version;
    }

    public static function getUBModules()
    {
        return [
            //coming soon
        ];
    }

}
