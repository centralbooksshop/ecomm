<?php

namespace Retailinsights\SmsOnOrderStatusChange\Helper;

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
	protected $transportBuilder;
    protected $storeManager;
	protected $inlineTranslation;
	protected $customerFactory;
	
	public function __construct(
		\Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state
    )
    {
    	$this->customerFactory = $customerFactory->create();
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        parent::__construct($context);
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
	
    public function SendSms($msg,$DReports,$mobile)
    {
		$apiProvider = $this->getGeneralConfig('apiprovider');
		if ($apiProvider == 'smscountry') {
			$user = $this->getGeneralConfig('sms_username');
			$password = $this->getGeneralConfig('sms_password');
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
				
			}
			catch(Exception $e) {
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

	public function AppSendSms($msg,$DReports,$mobile)
    {
		$apiProvider = $this->getGeneralConfig('apiprovider');
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
				$ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$curlresponse = curl_exec($ch);
				curl_close($ch);
				return $curlresponse;
			} catch(Exception $e) {
			   return false;
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
				curl_close($ch);
				return $curlresponse;
			}
			catch(Exception $e) {
			  echo false;
			}
		}
	}


	public function SendSmsShipment($msg,$DReports,$mobile)
    {
		$apiProvider = $this->getGeneralConfig('apiprovider');
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
			} catch(Exception $e) {
			  echo false;
			}
		}
	}

	public function emailOtp($incrementid,$email){
        if($email!=''){
            try{
                $recipientEmail = $email;
                

                $storeId = $this->storeManager->getStore()->getId();
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
                $collectionEmail = $this->customerFactory->setWebsiteId($websiteId)->loadByEmail($email);

                $identifier = 4;  // Enter your email template identifier here

                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($identifier)
                    ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                    ->setTemplateVars([
                        // 'name'  => 'name',
                        'incrementid'  => $incrementid,
                        'name' =>$collectionEmail->getName()
                    ])
                    ->setFrom('general')
                    ->addTo([$recipientEmail])
                    ->getTransport();
                $transport->sendMessage();
            }catch(\Exception $e){
                $this->messageManager->addError(__('Something went wrong while sending Mail. Please try again later.'));
            }
        }
    }
}