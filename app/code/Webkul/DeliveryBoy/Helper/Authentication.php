<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Helper;

use Webkul\DeliveryBoy\Encryption\EncryptorInterface;

class Authentication extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_USERNAME = 'deliveryboy/auth/username';
    public const XML_PATH_PASSWORD = 'deliveryboy/auth/password';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param EncryptorInterface $encryptor
     * @param \Magento\Framework\Encryption\EncryptorInterface $mageEncryptor
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        EncryptorInterface $encryptor,
        \Magento\Framework\Encryption\EncryptorInterface $mageEncryptor
    ) {
        parent::__construct($context);
        
        $this->scopeConfig = $context->getScopeConfig();
        $this->sessionManager = $sessionManager;
        $this->encryptor = $encryptor;
        $this->mageEncryptor = $mageEncryptor;
    }

    /**
     * Get User name.
     *
     * @return string
     */
    private function getUsername(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_USERNAME);
    }

    /**
     * Get Password.
     *
     * @return string
     */
    private function getPassword(): string
    {
        $pass =  $this->scopeConfig->getValue(self::XML_PATH_PASSWORD);
        return $this->mageEncryptor->decrypt($pass);
    }

    /**
     * Is Authorized.
     *
     * @param string $authKey
     * @return array
     */
    public function isAuthorized(string $authKey): array
    {
        $authData = [];
        $authData["code"] = 2;
        $authData["token"] = "";
        $h2 = $this->getCurrentAuthKey();
        $sessionId = $this->sessionManager->getSessionId();
        if ($authKey === $h2) {
            $authData["code"] = 1;
        } else {
            $authData["token"] = $sessionId;
        }
        return $authData;
    }

    /**
     * Get current Auth Key
     *
     * @return string
     */
    public function getCurrentAuthKey()
    {
        $username = $this->getUsername();
        $password = $this->getPassword();
        $sessionId = $this->sessionManager->getSessionId();
        $h1 = $this->encryptor->getSha256Hash($username.":".$password);
        $h2 = $this->encryptor->getSha256Hash($h1.":".$sessionId);
        return $h2;
    }
}
