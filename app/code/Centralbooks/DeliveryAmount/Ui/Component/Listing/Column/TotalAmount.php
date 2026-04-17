<?php
namespace Centralbooks\DeliveryAmount\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class TotalAmount extends Column
{
    protected $totalAmount;
    protected $deliveryboyF;
    protected $helper;
    protected $deliveryPartner;
    protected $logger;
    
    public function __construct(
	\Psr\Log\LoggerInterface $logger,    
	\Centralbooks\DeliveryPartner\Model\PartnerFactory $deliveryPartner,
	\Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboyF,
        \Centralbooks\DeliveryAmount\Helper\Data $hData,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [], array $data = [])
    {
	$this->logger 		       = $logger;
	$this->deliveryPartner = $deliveryPartner;
	$this->deliveryboyF = $deliveryboyF;
        $this->helper = $hData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
	    if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
                    $id = $this->getData("name");
		    $deliveryboyId = $item['id']; 
		    if(!empty($deliveryboyId)){
			     $deliveryboy_custom = $this->deliveryboyF->create()->load($deliveryboyId);
			     $driverType = $deliveryboy_custom->getDriverType();
                             $driverPartnerType = $deliveryboy_custom->getPartnerType();
			     if(($driverType == "Child") || ($driverType == "Parent" && $driverPartnerType == 2)){
			     	$deliveryBoyOrder = $this->helper->getTotalAmount($deliveryboyId);
			    	$totalAmount = $deliveryBoyOrder[0];
			     	$deliveryboy_custom->setTotalAmountToBeReceived($totalAmount)->save();
                             	$item[$id] = $totalAmount;
			     }else{
		//		if($driverPartnerType == 4){
					$totalAmounData = $this->helper->getParentAmount($driverPartnerType);
					$totalAmount =  $totalAmounData[0];
				     $this->logger->info("bardock ". $driverPartnerType." ".$deliveryboyId." ".$totalAmount);       
				     $deliveryboy_custom->setTotalAmountToBeReceived($totalAmount)->save();
				      $test =  $deliveryboy_custom->getTotalAmountToBeReceived();
				$item[$id] = $test; 
				
		//		}

			       }
			    }
			
                      }
	    }
        
        return $dataSource;
    }
}

