<?php
namespace Retailinsights\CourierAvailability\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public function executeCurl($url, $type, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($type === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        }

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function getScopeConfig($configPath)
    {
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    public function getApiUrl($type)
    {
        $isProd = $this->getScopeConfig('delhivery_lastmile/general/is_production');
        switch ($type) {
            case 'fetchPIN':
                return $isProd
                    ? 'https://track.delhivery.com/c/api/pin-codes/'
                    : 'https://staging-express.delhivery.com/c/api/pin-codes/';
        }
        return null;
    }
}
