<?php
namespace Centralbooks\Elasticrun\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    public function callElasticrunApi($data)
    {
        $apiUrl  = $this->getConfigValue('elasticrun_configuration/general/api_url');
        $apiToken = $this->getConfigValue('elasticrun_configuration/general/api_token');

        $headers = [
            "Authorization: {$apiToken}",
            "Content-Type: application/json"
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('Elasticrun API Error: ' . $error);
        }

        return json_decode($response, true);
    }

	public function executeCurl($consignment)
	{
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/elasticrun.log');
        $this->customLogger = new \Zend_Log();
        $this->customLogger->addWriter($writer);

		if (empty($consignment)) {
			return false;
		}

		$consignmentStatusApiUrl = $this->getConfigValue('elasticrun_configuration/general/status_api_url');

		$this->customLogger->info('consignmentStatusApiUrl '. $consignmentStatusApiUrl);

		if (!$consignmentStatusApiUrl) {
			return false;
		}

		$apiUrl = rtrim($consignmentStatusApiUrl, '?') . '?consignment=' . urlencode($consignment);
		$this->customLogger->info('consignmentStatusApiUrl '. $apiUrl);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			curl_close($ch);
			return false;
		}

		curl_close($ch);
		return $response;
	}


}
