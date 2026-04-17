<?php
namespace Centralbooks\Amazon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Psr\Log\LoggerInterface;

class Auth extends AbstractHelper
{
    const XML_PATH_CLIENT_ID = 'amazon_configuration/general/client_id';
	const XML_PATH_TOKEN_URL = 'amazon_configuration/general/token_api_url';
    const XML_PATH_CLIENT_SECRET = 'amazon_configuration/general/client_secret';
    const XML_PATH_REFRESH_TOKEN = 'amazon_configuration/general/refresh_token';
    const XML_PATH_ACCESS_TOKEN = 'amazon_configuration/general/x_amz_access_token';
    const XML_PATH_TOKEN_EXPIRES_AT = 'amazon_configuration/general/x_amz_expires_at';

    protected $scopeConfig;
    protected $configWriter;
    protected $logger;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->logger = $logger;
    }

    /**
     * Refreshes Amazon access token using stored refresh_token.
     * Returns array [bool success, array|string result]
     */
    public function refreshAccessToken()
    {
        try {
			$tokenUrl = trim($this->scopeConfig->getValue(self::XML_PATH_TOKEN_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $clientId = trim($this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $clientSecret = trim($this->scopeConfig->getValue(self::XML_PATH_CLIENT_SECRET, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $refreshToken = trim($this->scopeConfig->getValue(self::XML_PATH_REFRESH_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

            if (empty($clientId) || empty($clientSecret) || empty($refreshToken)) {
                return [false, 'Missing client_id / client_secret / refresh_token in config.'];
            }

            //$tokenUrl = 'https://api.amazon.com/auth/o2/token';

            $postFields = http_build_query([
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]);

            $ch = curl_init($tokenUrl);
            $headers = [
                'Content-Type: application/x-www-form-urlencoded'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                $this->logger->error('Amazon token request cURL error: ' . $err);
                return [false, 'cURL error: ' . $err];
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                $this->logger->error('Amazon token endpoint HTTP ' . $httpCode . ': ' . $response);
                return [false, "HTTP {$httpCode}: " . $response];
            }

            $decoded = json_decode($response, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Amazon token endpoint returned invalid JSON: ' . json_last_error_msg());
                return [false, 'Invalid JSON response from token endpoint'];
            }

            if (isset($decoded['access_token'])) {
                $accessToken = $decoded['access_token'];
                $expiresIn = isset($decoded['expires_in']) ? (int)$decoded['expires_in'] : null;

                // persist access token and expiry (store-level config)
                $this->configWriter->save(self::XML_PATH_ACCESS_TOKEN, $accessToken);
                if ($expiresIn) {
                    $expiresAt = time() + $expiresIn - 30; // subtract 30s as buffer
                    $this->configWriter->save(self::XML_PATH_TOKEN_EXPIRES_AT, $expiresAt);
                }

                $this->logger->info('Amazon access token refreshed successfully.');
                return [true, $decoded];
            }

            // if token not present, return full response for debugging
            $this->logger->warning('Amazon token response did not contain access_token: ' . json_encode($decoded));
            return [false, $decoded];

        } catch (\Exception $e) {
            $this->logger->error('Exception while refreshing Amazon token: ' . $e->getMessage());
            return [false, 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Returns stored access token if present and not expired (best-effort).
     * You can call refreshAccessToken() when missing/expired.
     */
    public function getAccessToken()
    {
        $token = trim((string)$this->scopeConfig->getValue(self::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $expiresAt = (int)$this->scopeConfig->getValue(self::XML_PATH_TOKEN_EXPIRES_AT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!empty($token) && $expiresAt > time()) {
            return $token;
        }

        // token missing or expired — try refreshing
        list($ok, $result) = $this->refreshAccessToken();
        if ($ok && isset($result['access_token'])) {
            return $result['access_token'];
        }

        return null;
    }
}
