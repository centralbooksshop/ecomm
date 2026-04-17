<?php
namespace MageArray\AddressAutoComplete\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ENABLE = 'addressautocomplete/general/enable';
    const XML_PATH_GOOGLE_API = 'addressautocomplete/general/google_api';

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function isEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getGoogleApi($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GOOGLE_API,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
