<?php
namespace Centralbooks\DeliveryAmount\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class NBoxDelivered extends Column
{
    protected $deliveryboyBoxesDelivered;
    protected $deliveryboyF;
    protected $helper;

    public function __construct(
        \Centralbooks\DeliveryAmount\Helper\Data $hData,
        \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboyF,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [], array $data = [])
    {
        $this->helper = $hData;
        $this->deliveryboyF = $deliveryboyF;
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
                     	 $deliveryboyBoxesDelivered = $this->helper->getNoOfBoxes($deliveryboyId);
                      	 $deliveryboy_custom->setNoOfBoxesDelivered($deliveryboyBoxesDelivered)->save();
		         $item[$id] = $deliveryboyBoxesDelivered;
		       }else{
		       
			       //	if($driverPartnerType == 4){
			          $totalBoxes = $this->helper->getParentBoxes($driverPartnerType);       
                                   $deliveryboy_custom->setNoOfBoxesDelivered($totalBoxes)->save();
                                   $test =  $deliveryboy_custom->getNoOfBoxesDelivered();
                                   $item[$id] = $test;
	
		       	//	}
		       }
                }
            }
        }
        return $dataSource;
    }

}


