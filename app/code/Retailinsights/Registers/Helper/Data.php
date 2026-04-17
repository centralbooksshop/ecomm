<?php

namespace Retailinsights\Registers\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
	const XML_PATH_RETAILINSIGHTS = 'retailinsights_entry/general/';
	const XML_PATH_RETAILINSIGHTS_VCON = 'retailinsights_entry/vcon/';
	const XML_PATH_EMAIL_TEMPLATE = 'section/email/email_template';
protected $customerFactory;
	protected $transportBuilder;
    protected $storeManager;
	protected $inlineTranslation;
	protected $wishlistHelper;
	protected $wishlist;
	protected $customerSession;
	
	public function __construct(
		\Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        StateInterface $state
    )
    {
    	$this->customerFactory = $customerFactory->create();
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->wishlistHelper = $wishlistHelper;
        $this->wishlist = $wishlist;
        $this->customerSession = $customerSession;
        $this->inlineTranslation = $state;
        parent::__construct($context);
	}
	

	public function sendEmail($email)
    {
		$templateId = 'send_otp_email_template'; // template id
        $fromEmail = 'info@centralbooksonline.com';  // sender Email id
        $fromName = 'Admin';             // sender Name
		$toEmail = $email; // receiver email id
		$from = ['email' => $fromEmail, 'name' => $fromName];

		$store = $this->storeManager->getStore()->getId();
		
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		try {
			// calculate otp
			$otp=$this->GenrateOtp("Use code",'To Login for centralbooksonline account',"Y",$email,'otp');
		
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions([
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
					'store' => $store
				])
                ->setTemplateVars([
                    'storename' => $this->storeManager->getStore()->getName(),
					'email' => $toEmail,
					'otp' =>"Your OneTime Password(OTP) is :" .$_SESSION["otp"]
                ])
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
			$this->inlineTranslation->resume();
			echo 'mail sent';
 
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }


	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}
	public function getGeneralConfig($code, $storeCode = null){
		return $this->getConfigValue(self::XML_PATH_RETAILINSIGHTS.$code,$storeCode);  
	}

	public function getVconConfig($code, $storeCode = null){
		return $this->getConfigValue(self::XML_PATH_RETAILINSIGHTS_VCON.$code,$storeCode);  
	}

	public function GenrateOtp($prefix,$info,$DReports,$mobile,$type){
		$otp = rand(10000,99999);
		$_SESSION[$type] = $otp;
		return $otp;
		// $this->SendSms($prefix,$info,$DReports,$otp,$mobile);
	}

	public function SendSms($prefix,$info,$DReports,$otp,$mobile)
    {
		$apiProvider = $this->getGeneralConfig('apiprovider');
		$msg = "$prefix $otp $info" ;
		if ($apiProvider == 'smscountry') {
			$user=$this->getGeneralConfig('sms_username');
			$password =$this->getGeneralConfig('sms_password');
			$url = $this->getGeneralConfig('sms_url');
			try{
				$ch = curl_init();
				$ret = curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt ($ch, CURLOPT_POSTFIELDS,"User=$user&passwd=$password&mobilenumber=$mobile&message=$msg&DR=$DReports");
				$ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$curlresponse = curl_exec($ch);
			} catch(Exception $e) {
			  echo false;
		    }
		} else if ($apiProvider == 'vcon') {
            $user = $this->getVconConfig('vcon_username');
			$password = $this->getVconConfig('vcon_password');
			$url = $this->getVconConfig('vcon_sms_url');
			$from_name = $this->getVconConfig('from_name');
			$unicode = $this->getVconConfig('vcon_unicode');
			try{
				$ch = curl_init();
				$ret = curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt ($ch, CURLOPT_POSTFIELDS,"username=$user&password=$password&unicode=$unicode&from=$from_name&to=$mobile&text=$msg");
				$ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$curlresponse = curl_exec($ch);
			}
			catch(Exception $e) {
			  echo false;
			}
		}
	}

	public function isWishlist($product_id) {
		$_in_wishlist  = '';
		if($this->customerSession->isLoggedIn()) {
			$id =  $this->customerSession->getCustomerID();
			$wishlist_collection = $this->wishlist->loadByCustomerId($id , true)->getItemCollection();
			foreach ($wishlist_collection as $wishlist_product):
				if($product_id == $wishlist_product->getProduct()->getId()){
					$_in_wishlist = "wish-added";
					break;
				}
			endforeach;
		} 
		return $_in_wishlist;
	}
}