<?php
namespace Centralbooks\Smcs\Cron;

use Psr\Log\LoggerInterface;
use Centralbooks\Smcs\Helper\Api;
use Centralbooks\Smcs\Model\TokenFactory;

class RefreshToken
{
    protected $api;
    protected $logger;
    protected $customLogger;
    protected $tokenFactory;

    public function __construct(
        Api $api,
        TokenFactory $tokenFactory,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->tokenFactory = $tokenFactory;
        $this->logger = $logger;

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/smcs.log');
        $this->customLogger = new \Zend_Log();
        $this->customLogger->addWriter($writer);
    }

    /*public function execute()
    {
        try {

            $tokenModel = $this->tokenFactory->create()->load(1);

            // If token exists and not expired, skip login
            if ($tokenModel->getAuthToken() && $tokenModel->getExpiresAt()) {

                $expiryTime = strtotime($tokenModel->getExpiresAt());

                if ($expiryTime > time()) {
                    $this->customLogger->info('SMCS Token still valid. Skipping login.');
                    return;
                }
            }

            // Token expired or not exists login
            $response = $this->api->login();

            $this->customLogger->info('SMCS Login Response: ' . print_r($response, true));

            if (!empty($response['success']) && $response['success'] == 1) {

                $tokenModel->setData([
                    'entity_id'  => 1,
                    'auth_token' => $response['AuthToken'],
                    'expires_at' => $response['TokenExpiredOn'],
                    'is_dp'      => $response['data']['IsDP'],
                    'user_id'    => $response['data']['UserID']
                ]);

                $tokenModel->save();

                $this->customLogger->info('SMCS Token Saved Successfully.');
            } else {
                $this->customLogger->info('SMCS Login Failed.');
            }

        } catch (\Exception $e) {
            $this->logger->error('SMCS Cron Error: ' . $e->getMessage());
        }
    }*/

	public function execute()
	{
		try {

			$tokenModel = $this->tokenFactory->create();
			$existing = $tokenModel->getCollection()->getFirstItem();

			if ($existing->getId()) {
				$tokenModel = $existing;

				if ($tokenModel->getExpiresAt() &&
					strtotime($tokenModel->getExpiresAt()) > time()) {

					$this->customLogger->info('SMCS Token still valid.');
					return;
				}
			}

			$response = $this->api->login();

			$this->customLogger->info('SMCS Login Response: ' . print_r($response, true));

			if (!empty($response['success']) && $response['success'] == 1) {

				$tokenModel->setData([
					'auth_token' => $response['AuthToken'],
					'expires_at' => $response['TokenExpiredOn'],
					'is_dp'      => $response['data']['IsDP'],
					'user_id'    => $response['data']['UserID']
				]);

				$tokenModel->save();

				$this->customLogger->info('SMCS Token Saved Successfully.');
			}

		} catch (\Exception $e) {
			$this->logger->error('SMCS Cron Error: ' . $e->getMessage());
		}
	}
}