<?php

namespace Centralbooks\ErpApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Centralbooks\ErpApi\Helper
 */
class Data extends AbstractHelper
{
    /**
     *
     */
    const ERP_ENABLE = 'erp/general/enabled';
    /**
     *
     */
    const ERP_CUSTOM_INDEXER_ENABLE = 'erp/general/customindexer';
    /**
     *
     */
    const ERP_ENDPOINT = 'erp/erpapicredential/endpoint';
    /**
     *
     */
    const ERP_ENVIRONMENT = 'erp/erpapicredential/environment';
    /**
     *
     */
    const ERP_PAGETYPE = 'erp/erpapicredential/pagetype';
    /**
     *
     */
    const ERP_APIGROUP = 'erp/erpapicredential/apigroup';
	/**
     *
     */
    const ERP_INVOICE_APIGROUP = 'erp/erpapicredential/invoiceapigroup';
	/**
     *
     */
    const ERP_LEDGER_APIGROUP = 'erp/erpapicredential/ledgerapigroup';
	
	 /**
     *
     */
    const ERP_COMPANY = 'erp/erpapicredential/company';
    /**
     *
     */
    const ERP_PREFER = 'erp/erpapicredential/prefer';
	
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function erpEnable()
    {
        return $this->scopeConfig->getValue(self::ERP_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function customreindexEnable()
    {
        return $this->scopeConfig->getValue(self::ERP_CUSTOM_INDEXER_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getStoreURL($storeId)
    {
        return $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getErpApiURL()
    {
        $endpoint = $this->scopeConfig->getValue(self::ERP_ENDPOINT, ScopeInterface::SCOPE_STORE);
		$environment = $this->scopeConfig->getValue(self::ERP_ENVIRONMENT, ScopeInterface::SCOPE_STORE);
		$pagetype = $this->scopeConfig->getValue(self::ERP_PAGETYPE, ScopeInterface::SCOPE_STORE);
		$apigroup = $this->scopeConfig->getValue(self::ERP_APIGROUP, ScopeInterface::SCOPE_STORE);
		$company = $this->scopeConfig->getValue(self::ERP_COMPANY, ScopeInterface::SCOPE_STORE);
		$erp_apiUrl = $endpoint . "/" . $environment . "/" . $pagetype . "/" . $apigroup . "/" . $company . "/";
		return $erp_apiUrl;
    }

	public function getInvoiceErpApiURL()
    {
        $endpoint = $this->scopeConfig->getValue(self::ERP_ENDPOINT, ScopeInterface::SCOPE_STORE);
		$environment = $this->scopeConfig->getValue(self::ERP_ENVIRONMENT, ScopeInterface::SCOPE_STORE);
		$pagetype = $this->scopeConfig->getValue(self::ERP_PAGETYPE, ScopeInterface::SCOPE_STORE);
		$apigroup = $this->scopeConfig->getValue(self::ERP_INVOICE_APIGROUP, ScopeInterface::SCOPE_STORE);
		$company = $this->scopeConfig->getValue(self::ERP_COMPANY, ScopeInterface::SCOPE_STORE);
		$invoice_erp_apiUrl = $endpoint . "/" . $environment . "/" . $pagetype . "/" . $apigroup . "/" . $company . "/";
		return $invoice_erp_apiUrl;
    }
     
	public function getLedgerApiURL()
    {
        $endpoint = $this->scopeConfig->getValue(self::ERP_ENDPOINT, ScopeInterface::SCOPE_STORE);
		$environment = $this->scopeConfig->getValue(self::ERP_ENVIRONMENT, ScopeInterface::SCOPE_STORE);
		$pagetype = $this->scopeConfig->getValue(self::ERP_PAGETYPE, ScopeInterface::SCOPE_STORE);
		$apigroup = $this->scopeConfig->getValue(self::ERP_LEDGER_APIGROUP, ScopeInterface::SCOPE_STORE);
		$company = $this->scopeConfig->getValue(self::ERP_COMPANY, ScopeInterface::SCOPE_STORE);
		$erp_ledger_apiUrl = $endpoint . "/" . $environment . "/" . $pagetype . "/" . $apigroup . "/" . $company . "/";
		return $erp_ledger_apiUrl;
    }
	

    /**
     * @param int $api_url_key
     * @return string
     */
    public function apiCall($api_url_key ,$apiUrl)
    {
            $this->customLog("=================================================");
            $this->customLog("apiCall call");
            $this->customLog($api_url_key);
			$this->customLog($apiUrl);

        try {
            if ($this->erpEnable()) 
			{
                if(empty($apiUrl)) {
				   $erp_base_apiUrl = $this->getErpApiURL();
				   $apiUrl = $erp_base_apiUrl . $api_url_key;
				}

				$pagesize = $this->scopeConfig->getValue(self::ERP_PREFER, ScopeInterface::SCOPE_STORE);
				//$accesstoken = $this->scopeConfig->getValue(self::ERP_ACCESS_TOKEN, ScopeInterface::SCOPE_STORE);
				//$filteroption = '?$filter=locationType eq '."'Retail'";
				$filteroptval = 'Retail';
				$filter = 'locationType eq \''.$filteroptval.'\'';
				$urlWithFilter = $apiUrl . '?$filter=' . urlencode($filter); 

                $postUrl = '';
				if($api_url_key =='locations'){
				  $postUrl = $urlWithFilter;
				} else {
				  $postUrl = $apiUrl;
				}

				$clientId = '8dc17b6f-103b-480f-8fec-eeabad04c047';
				$clientSecret =  'eNY8Q~.svtE-nsnvZ~xJn5fkvL1JkWnNDwaifb-G'; //'yQ-8Q~fWBLlTW1wt-_5I3_6TNdZhqcERaple2bsS';
				$tenantId = '2fb3e83f-4bff-43ab-bdc0-2322285eb593';
				$url_AccessToken = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
				$scope = 'https://api.businesscentral.dynamics.com/.default';
				// erp access token api
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url_AccessToken);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					'grant_type' => 'client_credentials',
					 'client_id' => $clientId,
					'client_secret' => $clientSecret,
					'scope' => $scope,
					));

				$data = curl_exec($ch);
				if (curl_error($ch)) {
				  $error_msg = curl_error($ch);
				}
				curl_close($ch);
				$data_obj = json_decode($data);
			    $access_token = $data_obj->{"access_token"};

                // erp all api 
				$headers = array(
				   'Content-Type: application/json',
				   'prefer: odatamaxpagesize='. $pagesize,
				   'Authorization: Bearer '. $access_token
				);

				$curl = curl_init( $postUrl );
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
				curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt( $curl, CURLOPT_FAILONERROR, true);
				$response = curl_exec( $curl );
                if (curl_error($curl)) {
					$error_msg = curl_error($curl);
				}
				//$response_obj = json_decode($response);
				//echo '<pre>';print_r($response_obj);echo '</pre>';
				curl_close($curl);
				
				if (isset($error_msg)) {
					 $this->customLog($error_msg);
					 return $error_msg;
				   
				} else {
					 $this->customLog($response);
					 return $response;
				}

			    /*$msg = urlencode($msg);
				$postUrl = $url . "?authkey=" . $apikey . "&sender=" . $senderid . "&mobiles=" . $mobilenumber . "&route=" . $msgtype . "&message=" . $msg . "&DLT_TE_ID=" . $tmpid;
				$curl = curl_init();
				curl_setopt_array($curl, [
					CURLOPT_URL => $postUrl,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "GET",
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);*/
                 
            }
        } catch (\Exception $e) {
            $this->customLog($e->getMessage());
        }
    }

	/**
     * @param int $api_url_key
     * @return string
     */
    public function apiInvoiceCall($api_url_key ,$invoicepostUrl, $jayParsedAry)
    {
            $this->customLog("=================================================");
            $this->customLog("apiInvoice call");
            $this->customLog($api_url_key);
	    $this->customLog($invoicepostUrl);
	    $this->customLog("json encode data :". json_encode($jayParsedAry));


        try {
            if ($this->erpEnable()) 
			{
				$pagesize = $this->scopeConfig->getValue(self::ERP_PREFER, ScopeInterface::SCOPE_STORE);
				//$accesstoken = $this->scopeConfig->getValue(self::ERP_ACCESS_TOKEN, ScopeInterface::SCOPE_STORE);

				$clientId = '8dc17b6f-103b-480f-8fec-eeabad04c047';
				$clientSecret = 'eNY8Q~.svtE-nsnvZ~xJn5fkvL1JkWnNDwaifb-G';
				$tenantId = '2fb3e83f-4bff-43ab-bdc0-2322285eb593';
				$url_AccessToken = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
				$scope = 'https://api.businesscentral.dynamics.com/.default';
				// erp access token api
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url_AccessToken);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					'grant_type' => 'client_credentials',
					 'client_id' => $clientId,
					'client_secret' => $clientSecret,
					'scope' => $scope,
					));

				$data = curl_exec($ch);
				if (curl_error($ch)) {
				  $error_msg = curl_error($ch);
				}
				curl_close($ch);
				$data_obj = json_decode($data);
			    $access_token = $data_obj->{"access_token"};

                // erp all api 
				$headers = array(
				   'Content-Type: application/json',
				   'prefer: odatamaxpagesize='. $pagesize,
				   'Authorization: Bearer '. $access_token
				);

				$curl = curl_init( $invoicepostUrl );
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($jayParsedAry));
				curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt( $curl, CURLOPT_FAILONERROR, true);
				$response = curl_exec( $curl );
                if (curl_error($curl)) {
					$error_msg = curl_error($curl);
				}
				//$response_obj = json_decode($response);
				//echo '<pre>';print_r($response_obj);echo '</pre>';die;
				curl_close($curl);
				
				if (isset($error_msg)) {
					 $this->customLog($error_msg);
					 return $error_msg;
				   
				} else {
					 $this->customLog($response);
					 return $response;
				}
                 
            }
        } catch (\Exception $e) {
            $this->customLog($e->getMessage());
        }
    }

     public function getAuthToken()
    {
       try {
            if ($this->erpEnable())
			{
               	$clientId = '8dc17b6f-103b-480f-8fec-eeabad04c047';
				$clientSecret = 'eNY8Q~.svtE-nsnvZ~xJn5fkvL1JkWnNDwaifb-G';
				$tenantId = '2fb3e83f-4bff-43ab-bdc0-2322285eb593';
				$url_AccessToken = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
				$scope = 'https://api.businesscentral.dynamics.com/.default';

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url_AccessToken);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					'grant_type' => 'client_credentials',
					 'client_id' => $clientId,
					'client_secret' => $clientSecret,
					'scope' => $scope,
					));

				$data = curl_exec($ch);
				if (curl_error($ch)) {
				  $error_msg = curl_error($ch);
				}
				curl_close($ch);
				$data_obj = json_decode($data);
			    return $data_obj->{"access_token"};

            }
        } catch (\Exception $e) {
            $this->customLog($e->getMessage());
        }
    }


    /**
     * @param $log
     * @throws \Zend_Log_Exception
     */
    public function customLog($log)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/erpapi.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r($log, true));
    }

	public function MagentoApi()
    {
		$baseUrl = "https://dev.centralbookshop.in/"; 
		$userData = array("username" => "ravi", "password" => "Ravi@2023");

		$ch = curl_init($baseUrl."/rest/V1/integration/admin/token");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));
		$token = curl_exec($ch);
		//echo '<pre>';print_r($token);echo '</pre>';
		$ch = curl_init($baseUrl."/rest/all/V1/configurable-products/8902519003294/children");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));
		$result = curl_exec($ch);
		$result = json_decode($result, 1);
		//echo '<pre>';print_r($result);echo '</pre>';
		$err = curl_error($ch);
		curl_close($ch);
	}

	public function GenerateToken()
    {

		
	}

}
