<?php
namespace Centralbooks\Smcs\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH = 'smcs_configuration/general/';

    public function getConfig($field)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH . $field,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBaseUrl()
    {
        return $this->getConfig('base_url');
    }

    public function getClientCode()
    {
        return $this->getConfig('client_code');
    }

    public function getSecretKey()
    {
        return $this->getConfig('secret_key');
    }

    public function getUsername()
    {
        return $this->getConfig('username');
    }

    public function getPassword()
    {
        return $this->getConfig('password');
    }

	public function isEnabled()
	{
		return $this->getConfig('enabled');
	}

	public function getPickupPincode()
    {
        return $this->getConfig('pickup_pincode');
    }
}