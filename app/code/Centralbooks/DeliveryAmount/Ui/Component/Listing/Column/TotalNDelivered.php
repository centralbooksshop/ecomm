<?php
namespace Centralbooks\DeliveryAmount\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class TotalNDelivered extends Column
{

    protected $deliveryboyBoxesDelivered;
    protected $deliveryboyCoversDelivered;
    protected $totalNDelivered;
    protected $deliveryboyF;
    protected $helper;

    public function __construct(
	\Centralbooks\DeliveryAmount\Helper\Data $hData,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboyF,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [], array $data = [])
    {
	    $this->helper = $hData;
            $this->collectionFactory = $collectionFactory;
            $this->deliveryboyF                = $deliveryboyF;

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
                            if(($driverType == "Child") || ($driverType == "Parent" && $driverPartnerType == 2) ){

				    $deliveryboyBoxesDelivered = $this->collectionFactory->create()
                                                         ->addFieldToFilter("deliveryboy_id", $deliveryboyId)
                                                         ->addFieldToFilter("order_status", 'order_delivered')
							 ->addFieldToFilter("package_type", 'Box')
							 ->addFieldToFilter("payment_status",array('null' => true))
							 ->getSize();



				    $deliveryboyCoversDelivered = $this->collectionFactory->create()
                                                         ->addFieldToFilter("deliveryboy_id", $deliveryboyId)
                                                         ->addFieldToFilter("order_status", 'order_delivered')
							 ->addFieldToFilter("package_type", 'Cover')
							 ->addFieldToFilter("payment_status",array('null' => true))
							 ->getSize();
                      	    $totalNDelivered = ($deliveryboyCoversDelivered + $deliveryboyBoxesDelivered);
                      	    	$deliveryboy_custom->setTotalNoOfOrders($totalNDelivered)->save();
			    	$item[$id] = $totalNDelivered;
			    }else{
                            //    if($driverPartnerType == 4){
                                  $totalOrders = $this->helper->getTotalOrders($driverPartnerType);      
                                   $deliveryboy_custom->setTotalNoOfOrders($totalOrders)->save();
                                   $test =  $deliveryboy_custom->getTotalNoOfOrders();
                                   $item[$id] = $test;

                              //  }
                       }			  
                }
            }
        }
        return $dataSource;
    }
}

