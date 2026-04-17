<?php

namespace Ecom\Ecomexpress\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Ecomexpress extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements 

\Magento\Shipping\Model\Carrier\CarrierInterface 

{
	protected $_code = 'ecomexpress';
	protected $price = '0';
	protected $_logger;

	protected $_isFixed = true;
	protected $_rateResultFactory;
	protected $_rateMethodFactory;
	protected  $_rateResultErrorFactory;

	protected  $trackFactory;
	protected  $trackErrorFactory;
	protected  $trackStatusFactory;
	

	public function __construct(

	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 

	\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory, 

	\Psr\Log\LoggerInterface $logger, 

	\Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory, 

	\Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory, 
	\Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
	\Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
	\Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
	

	array $data = []) 

	{
		$this->_rateResultFactory = $rateResultFactory;	
		$this->_rateMethodFactory = $rateMethodFactory;
		$this->_trackFactory  = $trackFactory;
        $this->_trackErrorFactory = $trackErrorFactory;
        $this->_trackStatusFactory  = $trackStatusFactory;
		$this->_rateResultErrorFactory= $rateErrorFactory;
		$this->_logger = $logger;
		$this->scopeconfig=$scopeConfig;
		parent::__construct ( $scopeConfig, $rateErrorFactory, $logger, $data );
	}


	public function getAllowedMethods() 
	{
		
		//return [$this->_code => $this->getConfigData ( 'title' ) ];

		return [$this->_code => "ecomexpress"];
	}

	public function collectRates(RateRequest $request) 
	{
		return false;
		
	}

	public function isTrackingAvailable(){
	
		return true;
	}
	
	public function getTracking($trackings)
    {
    	$result = $this->_trackFactory->create();
    	$track_url = 'https://ecomexpress.in/tracking/?awb_field=';
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('name'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("{$track_url}{$tracking}");
            $result->append($status);
        }
        $this->_result = $result;
        return $result;
    }

	/*public function getTrackingInfo($tracking_number)
	{
		$result = $this->_trackFactory->create();
		$tracking = $this->_trackStatusFactory->create();

		$tracking->setCarrier($this->_code);
		$tracking->setCarrierTitle('Carrier Title');
		$tracking->setTracking($tracking_number);
		$tracking->setUrl('https://ecomexpress.in/tracking/?awb_field=' . $tracking_number);
		

		$result->append($tracking);

		return $tracking;
	}*/

	public function getTrackingInfo($trackings)
    {
    	$result = $this->_trackFactory->create();
    	$track_url = 'https://ecomexpress.in/tracking/?awb_field=';
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('name'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("{$track_url}{$tracking}");
            $result->append($status);
        }
        //$this->_result = $result;
        return $status;
    }


	public function proccessAdditionalValidation(\Magento\Framework\DataObject $request) {
        return true;
    }
	public function processAdditionalValidation(\Magento\Framework\DataObject $request) {
        return true;
    }
	

	protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
      
    }



}