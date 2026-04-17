<?php
 
namespace Retailinsights\Registers\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory; 
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\EmailNotificationInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
 
class Emailverify extends \Magento\Framework\App\Action\Action
{
    
	 /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

	/**
     * Welcome email, when password setting is required
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

	/**
     * @deprecated
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    const XML_PATH_IS_CONFIRM = 'customer/create_account/confirm';

	/**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

	  /**
     * @var PsrLogger
     */

	 /**
     * @var Encryptor
     */
    private $encryptor;

    protected $logger;


	protected $_resultPageFactory;
    protected $_customer;
    protected $_customerFactory;
    protected $customerInfo;
    protected $storeManager;
    protected $messageManager;

	/**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

	/**
     * @var CustomerRegistry
     */
    private $customerRegistry;

	   /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;
	protected $resultRedirectFactory;
    
    public function __construct(
        Context $context, 
         \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerInfo,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
		\Magento\Framework\Controller\ResultFactory $resultRedirectFactory,
		CustomerRegistry $customerRegistry,
		PsrLogger $logger,
		Registry $registry,
		ScopeConfigInterface $scopeConfig,
		Encryptor $encryptor,
        StoreManagerInterface $storeManager
    )
    {
        $this->messageManager = $messageManager;
        $this->customerInfo = $customerInfo;
        $this->_customerFactory = $customerFactory;
		$this->customerRegistry = $customerRegistry;
		$this->logger = $logger;
        $this->_customer = $customers;
        $this->_resultPageFactory = $resultPageFactory;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->scopeConfig = $scopeConfig;
		$this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
		$this->registry = $registry;
        parent::__construct($context);
    }
 
    public function execute()
    {
		$resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account');
		$om = \Magento\Framework\App\ObjectManager::getInstance();  
		$customerSession = $om->get('Magento\Customer\Model\Session');  
		$customerData = $customerSession->getCustomer()->getData();
		$customer_id = $customerSession->getCustomer()->getId();
	    //echo '<pre>';print_r($customer);die;
        if($customer_id) {
            $redirectUrl = 'customer/account';
			$customer = $this->customerInfo->getById($customer_id);
			$this->sendEmailConfirmation($customer, $redirectUrl);
			$success_message = 'Please complete your email verification by clicking the link sent to your email ID.';
            $this->messageManager->addSuccessMessage($success_message);
			return $resultRedirect;
        }
    }

	 /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param CustomerInterface $customer
     * @param string $redirectUrl
     * @return void
     */
    protected function sendEmailConfirmation(CustomerInterface $customer, $redirectUrl)
    {
        try {
            //$hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
			$hash = 1;
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($customer) && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }


	/**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

	 /**
     * Check if accounts confirmation is required in config
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    protected function isConfirmationRequired($customer)
    {
        if ($this->canSkipConfirmation($customer)) {
            return false;
        }

        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $customer->getWebsiteId()
        );
    }

	   /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    protected function canSkipConfirmation($customer)
    {
        if (!$customer->getId()) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($customer->getEmail());
    }

	/**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }


}