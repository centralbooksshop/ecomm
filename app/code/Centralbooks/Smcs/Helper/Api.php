<?php
namespace Centralbooks\Smcs\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Api extends AbstractHelper
{
    protected $config;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Centralbooks\Smcs\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    public function call($endpoint, $payload = [], $token = null)
	{
		$url = rtrim($this->config->getBaseUrl(), '/') . $endpoint;

		$headers = [
			"Content-Type: application/json",
			"clientcode: " . $this->config->getClientCode()
		];

		if ($token) {
			$headers[] = "token: " . $token;
		}

		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($payload),
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_TIMEOUT => 30
		]);

		$response = curl_exec($ch);
		//print_r($response);die;
		$error = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($error) {
			return ["error" => $error];
		}

		if ($httpCode != 200) {
			return ["http_error" => $httpCode, "response" => $response];
		}

		return json_decode($response, true);
	}

    public function login()
	{
		$url = $this->config->getBaseUrl() . '/login';

		$payload = [
			"data" => [
				"login_username" => $this->config->getUsername(),
				"login_password" => $this->config->getPassword()
			]
		];

		$headers = [
			"Content-Type: application/json",
			"clientcode: " . $this->config->getClientCode(),
			"secretkey: " . $this->config->getSecretKey()
		];

		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($payload),
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_TIMEOUT => 30
		]);

		$response = curl_exec($ch);
		//print_r($response);die;
		$error = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($error) {
			return ["error" => $error];
		}

		if ($httpCode != 200) {
			return ["http_error" => $httpCode, "response" => $response];
		}

		return json_decode($response, true);
	}
}