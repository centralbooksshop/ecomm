<?php
namespace Centralbooks\DeliveryAmount\Controller\Adminhtml\Delivery;
use Centralbooks\DeliveryAmount\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Update extends \Magento\Backend\App\Action
{
        /**
        * @var Data
        */
	
	protected $helper;
	protected $_configWriter;
	protected $amount;
	protected $resource;
	const XML_ZONE_PRICE = 'deliveryamount/deliveryboyzone/zoneprice';

	/**
     	* Constructor
     	* @param Data $data
	*/

	public function __construct(
		\Magento\Framework\App\ResourceConnection $resource,	
		\Centralbooks\DeliveryAmount\Model\AmountFactory $amount,
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
		\Magento\Backend\App\Action\Context $context,
		Data $data
        )
	{
		$this->resource = $resource;
		$this->amount = $amount;
		$this->_configWriter = $configWriter;
		$this->helper = $data;
                parent::__construct($context);
        }

        public function execute()
	{
	   if($this->helper->erpEnable()) {
			$deliveryboy_api_key = 'deliveyboyZoneRate';
			$erp_base_apiUrl = $this->helper->getErpApiURL();
			$zoneApiURL =  $erp_base_apiUrl.$deliveryboy_api_key;
			$access_token = $this->helper->getErpToken();
			$headers = array(
				   'Content-Type: application/json',
				   'Authorization: Bearer '. $access_token
			);

				$curl = curl_init($zoneApiURL);
                		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
				curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt( $curl, CURLOPT_FAILONERROR, true);
				$response = curl_exec( $curl );
				// echo "<pre>"; print_r($response); echo "</pre>"; die;			
				if (!empty($response)) {
					$erpResponse = json_decode($response, true);
					$erpResponse = $erpResponse['value'];
					$zoneList = array_column($erpResponse, 'zone');
					$amount = array_column($erpResponse, 'rate');
					$packageType = array_column($erpResponse,'packageType');
					$eId = array_column($erpResponse,'id');
					$message;$data = array();
					foreach ($zoneList as $key => $value) {
						$value = ucfirst(strtolower($value));
						$existingData = $this->amount->create()->load($eId[$key]);
					if($existingData->getZoneToken() != NULL){
						$updataData = $existingData->setZoneList($value)
                                                                           ->setPackageType($packageType[$key])
                                                                           ->setDeliveryAmount($amount[$key])->save();
					}
						
					if($existingData->getZoneToken() == NULL){
						$insertData = $this->amount->create()->setZoneToken($eId[$key])
				  				           ->setZoneList($value)
                                                                           ->setPackageType($packageType[$key])
									   ->setDeliveryAmount($amount[$key])->save();
					}
					 
						$data[] = ($value.$packageType[$key].":".$amount[$key].";");
					        $message = 'This Delivery Amount Updation Success.';
					}
					$data = json_encode($data);
					$data = preg_replace('/[([",\])]/','', $data);
					$this->_configWriter->save(self::XML_ZONE_PRICE,$data,$scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
					$this->helper->cacheFlush();
					$this->messageManager->addSuccessMessage(__($message));
					$resultRedirect = $this->resultRedirectFactory->create();
					return $resultRedirect->setPath('*/*/amount');
			}else{
				$this->messageManager->addErrorMessage(__('This Delivery Amount Updation Failed.'));
				$resultRedirect = $this->resultRedirectFactory->create();
                                return $resultRedirect->setPath('*/*/amount');	
			} 
	    }
	}
}


