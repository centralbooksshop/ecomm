<?php

namespace Centralbooks\DeliveryAmount\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Class Data
 * @package Centralbooks\ErpApi\Helper
 */
class Data extends AbstractHelper
{
    const ERP_ENABLE = 'erp/general/enabled';
    const ERP_ENDPOINT = 'erp/erpapicredential/endpoint';
    const ERP_ENVIRONMENT = 'erp/erpapicredential/environment';
    const ERP_PAGETYPE = 'erp/erpapicredential/pagetype';
    const ERP_APIGROUP = 'erp/erpapicredential/apigroup';
    const ERP_INVOICE_APIGROUP = 'erp/erpapicredential/invoiceapigroup';
    const ERP_LEDGER_APIGROUP = 'erp/erpapicredential/ledgerapigroup';
    const ERP_COMPANY = 'erp/erpapicredential/company';
    const ERP_PREFER = 'erp/erpapicredential/prefer';
    protected $cfOrder;
    protected $deliveryboyOrderFactory;
    protected $deliveryboyBoxesDelivered;
    protected $deliveryboyCoversDelivered;
    protected $totalAmount;
    protected $cacheFrontendPool;
    protected $cacheTypeList;
    protected $deliveryboyF;
    protected $logger;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
	StoreManagerInterface $storeManager,
	TypeListInterface $cacheTypeList,
	\Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $cfOrder,
	\Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory,
	\Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
	\Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboyF,
	\Psr\Log\LoggerInterface $logger
    )
    {
	    $this->storeManager = $storeManager;
	    $this->cfOrder = $cfOrder;
	    $this->deliveryboyOrderFactory = $deliveryboyOrderFactory;
	    $this->cacheFrontendPool = $cacheFrontendPool;
	    $this->cacheTypeList =  $cacheTypeList;     
	    $this->deliveryboyF = $deliveryboyF;
	    $this->logger                  = $logger;
	    parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function erpEnable()
    {
        return $this->scopeConfig->getValue(self::ERP_ENABLE, ScopeInterface::SCOPE_STORE);
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
	
    public function getErpToken(){

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
			    return $access_token;
	}

    public function apiCall($api_url_key, $erpToken, $data, $headers, $vendorUpdatePayload){
        try {
            if ($this->erpEnable()) 
			{
				$api_url =  $this->getErpApiURL();
				if(!empty($erpToken)){		
					$updateErpURL = $api_url.$api_url_key."(".$erpToken.")";
					$curl = curl_init( $updateErpURL );
			                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
					curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt( $curl, CURLOPT_FAILONERROR, true);
					$response = curl_exec( $curl );
					if (isset($response)) {
						$erpResponse = json_decode($response, true);
						$driverEtag = $erpResponse['@odata.etag'];
						if (!empty($driverEtag)){
		                     		   $putHeaders = array(
				                   'Content-Type: application/json',
						   'Authorization: Bearer '. $this->getErpToken(),
						   'If-Match:'.$driverEtag
						   );
	                                $curl = curl_init($updateErpURL);
	                                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	                                curl_setopt( $curl, CURLOPT_HTTPHEADER, $putHeaders );
	                                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
	                                curl_setopt( $curl, CURLOPT_FAILONERROR, true);
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $vendorUpdatePayload);

					$response = curl_exec( $curl ); 
					if (curl_error($curl)) {
						$error_msg = curl_error($curl);
					}
					
					curl_close($curl);
					
					if (isset($error_msg)) {
						 $this->customLog($error_msg);
					 	 return $error_msg;
										   
					} else {
						$this->customLog($response);
						 return $response;      
					
					     }     
			   		  }			 
	               			}

				}else{
					$driver_api_key = 'apiDeliveryBoy';
					$erp_apiUrl =  $api_url.$driver_api_key;
					$curl = curl_init( $erp_apiUrl );
	                		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers);
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt( $curl, CURLOPT_FAILONERROR, true);
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $data);
					$response = curl_exec( $curl );
	                		if (curl_error($curl)) {
					    $error_msg = curl_error($curl);
					}
					curl_close($curl);
						if (isset($response)) {
							$erpResponse = json_decode($response, true);
							return $erpResponse;		
						}	
					}	
            }
        } catch (\Exception $e) {
            $this->customLog($e->getMessage());
        }
    }

    public function getTotalAmount($deliveryboyId){
	$data = array();
	$deliveryBoyOrder = $this->deliveryboyOrderFactory->create()->getCollection();
        $dboCollection    = $deliveryBoyOrder->addFieldToSelect(['id','delivery_amount'])
                                             ->addFieldToFilter('deliveryboy_id',$deliveryboyId)
                            	             ->addFieldToFilter('order_status','order_delivered')
					     ->addFieldToFilter('delivery_amount',['neq' => 'NULL'])
					     ->addFieldToFilter("payment_status",array('null' => true));
	$totalAmount      = array_sum($dboCollection->getColumnValues('delivery_amount'));
	$incrementId 	  = $dboCollection->getColumnValues('id');
	$data 		  = [$totalAmount, $incrementId];
	return $data;
    }

    public function getNoOfCovers($deliveryboyId)
    {
       	$deliveryboyCoversDelivered = $this->cfOrder->create()
                                           ->addFieldToFilter("deliveryboy_id", $deliveryboyId)
                                           ->addFieldToFilter("order_status", 'order_delivered')
					   ->addFieldToFilter("package_type", 'Cover')
				   	   ->addFieldToFilter("payment_status",array('null' => true))
					   ->getSize();
        return $deliveryboyCoversDelivered;
    }

    public function getNoOfBoxes($deliveryboyId)
    {
        $deliveryboyBoxesDelivered = $this->cfOrder->create()
                                          ->addFieldToFilter("deliveryboy_id", $deliveryboyId)
                                          ->addFieldToFilter("order_status", 'order_delivered')
					  ->addFieldToFilter("package_type", 'Box')
					  ->addFieldToFilter("payment_status",array('null' => true))
					  ->getSize();
        return $deliveryboyBoxesDelivered;
    }

    public function getParentAmount($driverPartnerType){
	    $data = array();
	    $deliveryBoyData = $this->deliveryboyF->create()->getCollection();
            $dbdCollection = $deliveryBoyData->addFieldToSelect(['id','total_amount_to_be_received','no_of_boxes_delivered','no_of_covers_delivered','total_no_of_orders'])
                                             ->addFieldToFilter('partner_type',$driverPartnerType)
					     ->addFieldToFilter('total_amount_to_be_received',['neq' => 'NULL'])
                                             ->addFieldToFilter('driver_type', 'Child');
	    $totalParentDriverAmount = array_sum($dbdCollection->getColumnValues('total_amount_to_be_received'));
//	    $this->logger->info("totalParentDriverAmount".print_r($totalParentDriverAmount, true));
	    $totalParentBoxes = array_sum($dbdCollection->getColumnValues('no_of_boxes_delivered'));
//	    $this->logger->info("totalParentBoxes".print_r($totalParentBoxes, true));
	    $totalParentCovers = array_sum($dbdCollection->getColumnValues('no_of_covers_delivered'));
	    $total_no_of_orders = array_sum($dbdCollection->getColumnValues('total_no_of_orders'));
	    $childId = $dbdCollection->getColumnValues('id');
//	    $this->logger->info("totalParentCovers".print_r($totalParentCovers, true));
//	    $this->logger->info("child ID : ".print_r($childId, true));
	    $deliveryBoyParentData = $this->deliveryboyF->create()->getCollection();
	    $partnerParent =  $deliveryBoyParentData->addFieldToSelect(['id'])
                                              ->addFieldToFilter('partner_type', 4)
                                              ->addFieldToFilter('driver_type', 'Parent');
	    $partnerParentID = $partnerParent->getFirstItem()->getId();
//	    $this->logger->info("partnerParentID".print_r($partnerParentID, true));   
	    $data = [$totalParentDriverAmount, $partnerParentID, $childId,$totalParentBoxes,$totalParentCovers, $total_no_of_orders];
            return $data;

    }

	
    public function getParentCovers($driverPartnerType){
            $deliveryBoyData = $this->deliveryboyF->create()->getCollection();
            $dbdCollection = $deliveryBoyData->addFieldToSelect(['no_of_covers_delivered'])
                                             ->addFieldToFilter('partner_type',$driverPartnerType)
                                             ->addFieldToFilter('driver_type', 'Child');

            $totalParentCovers = array_sum($dbdCollection->getColumnValues('no_of_covers_delivered'));
            $this->logger->info("totalParentCovers".print_r($totalParentCovers, true));
            return $totalParentCovers;

    }

    public function getTotalOrders($driverPartnerType){
            $deliveryBoyData = $this->deliveryboyF->create()->getCollection();
            $dbdCollection = $deliveryBoyData->addFieldToSelect(['total_no_of_orders'])
                                             ->addFieldToFilter('partner_type',$driverPartnerType)
                                             ->addFieldToFilter('driver_type', 'Child');

            $total_no_of_orders = array_sum($dbdCollection->getColumnValues('total_no_of_orders'));
            $this->logger->info("total_no_of_orders".print_r($total_no_of_orders, true));
            return $total_no_of_orders;

    }

    public function getParentBoxes($driverPartnerType){
            $deliveryBoyData = $this->deliveryboyF->create()->getCollection();
            $dbdCollection = $deliveryBoyData->addFieldToSelect(['no_of_boxes_delivered'])
                                             ->addFieldToFilter('partner_type',$driverPartnerType)
                                             ->addFieldToFilter('driver_type', 'Child');

            $totalParentBoxes = array_sum($dbdCollection->getColumnValues('no_of_boxes_delivered'));
            $this->logger->info("totalParentBoxes".print_r($totalParentBoxes, true));
            return $totalParentBoxes;

    }

   /* public function setparentData($deliveryPartnerId){
    	$deliveryBoyData = $this->getParentAmount($deliveryPartnerId);
        $parentAmountUpdate = $this->deliveryboyF->create()->load($deliveryBoyData[1]);
        $childIds = $deliveryBoyData[2]; $totalChildAmount = 0; $totalChildCovers = 0; $totalChildBox =0; $totalOrders = 0;
                           foreach($childIds as $childId){
                                             $childTotalAmount = $this->getTotalAmount($childId);
                                             $totalChildAmount += $childTotalAmount[0];

                                             $childTotalCovers = $this->getNoOfCovers($childId);
                                             $totalChildCovers += $childTotalCovers;

                                             $childTotalBoxes = $this->getNoOfBoxes($childId);
                                             $totalChildBox += $childTotalBoxes;
                            }
                            $totalOrders = ($totalChildCovers + $totalChildBox);
                          $this->logger->info("totalOrders : ". $totalOrders);
                            $parentAmountUpdate->setTotalAmountToBeReceived($totalChildAmount)->save();
                            $parentAmountUpdate->setNoOfCoversDelivered($totalChildCovers)->save();
                            $parentAmountUpdate->setNoOfBoxesDelivered($totalChildBox)->save();
                            $parentAmountUpdate->setTotalNoOfOrders($totalOrders)->save();    
    } */

    public function cacheFlush(){
      $_types = [ 'config' ]; 
 
    foreach ($_types as $type) {
        $this->cacheTypeList->cleanType($type);
    }
    foreach ($this->cacheFrontendPool as $cacheFrontend) {
        $cacheFrontend->getBackend()->clean();
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
}

