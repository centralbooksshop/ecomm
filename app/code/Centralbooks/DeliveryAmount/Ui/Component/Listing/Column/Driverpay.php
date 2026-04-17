<?php
namespace Centralbooks\DeliveryAmount\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Backend\Model\Auth\Session;


class Driverpay extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
    * @var Magento\Backend\Model\Auth\Session
    */
    protected $adminSession;
    protected $helper;
    protected $deliveryboyF;

    /**
     * Dependency Initilization
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
	\Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryboyF,
	\Centralbooks\DeliveryAmount\Helper\Data $hData,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
	UrlInterface $urlBuilder,
	Session $adminSession,
        array $components = [],
        array $data = []
    ) {
	$this->deliveryboyF = $deliveryboyF;
	$this->helper = $hData;
	$this->adminSession = $adminSession;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
	    $roleData = $this->adminSession->getUser()->getRole()->getData(); 

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
		 if (isset($item['id'])) {
		    $deliveryboyId = $item['id'];
                    $viewUrlPath = $this->getData('config/viewUrlPath');
		    $urlEntityParamName = $this->getData('config/urlEntityParamName');		
		    if(!empty($deliveryboyId)){
			    $deliveryBoyOrder = $this->helper->getTotalAmount($deliveryboyId);
			    $deliveryboyData = $this->deliveryboyF->create()->load($deliveryboyId);
			    $driverType = $deliveryboyData->getDriverType();
			    $driverPartnerType = $deliveryboyData->getPartnerType();
			    $deliveryPartnerData = $this->helper->getParentAmount($driverPartnerType);
			    $childIds = $deliveryPartnerData[2]; $totalChildAmount = 0;
                                     foreach($childIds as $childId){
                                             $childTotalAmount = $this->helper->getTotalAmount($childId);
                                             $totalChildAmount += $childTotalAmount[0];
                                     }

			    //$deliveryParentAmount = $deliveryPartnerData[0];
		    	    $totalAmount = $deliveryBoyOrder[0];
			    if(($roleData['role_name'] == "Administrators" && $totalAmount > 0 && $driverType == "Parent" || 
				$roleData['role_name'] == "Administrators" && $totalChildAmount > 0 && $driverType == "Parent") || 
				($roleData['role_name'] == "Delivery Amount" && $totalAmount > 0 && $driverType == "Parent" ||
			       $roleData['role_name'] == "Delivery Amount" && $totalChildAmount > 0 && $driverType == "Parent")
			    
			    ){
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item['id'],
                                ]
                            ),
                            'label' => __('Approve'),
                        ],
		    ];
		    }
		  }
                }
            }
        }
        return $dataSource;
    }
}
