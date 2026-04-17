<?php

namespace Centralbooks\ClickpostExtension\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    public function getConfig($scope, $path)
    {
        try {
            switch ($scope) {
                case 'store':
                    return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);        // From store view
                case 'website':
                    return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE);    // From Website
                default:
                    return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
            }
 
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getBaseUrl($scopeConfig, $organisationId)
    {
        $integration_url_hash = array(
            "1" => "dtdc_projectx_url"
        );

        $url_to_find = NULL;
        $integration_url = NULL;
        
        if (array_key_exists($organisationId, $integration_url_hash)) {
            $url_to_find = $integration_url_hash[ $organisationId ];
        }
        
        if (!is_null($url_to_find)) {
            $integration_url = $this->scopeConfig->getValue('configuration/services/' . $url_to_find, ScopeInterface::SCOPE_STORE);
        }

        if (is_null($integration_url)) {
            $integration_url = $this->scopeConfig->getValue('configuration/services/shipsy_url', ScopeInterface::SCOPE_STORE);
        }

        return $integration_url;
    }
}
