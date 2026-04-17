<?php
namespace Retailinsights\ProcessCBOOrders\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Retailinsights\ProcessCBOOrders\Model\ResourceModel\DeliveredCBOOrders\Collection as DriverOrderCollF;

class ReturnAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
	protected $reasonCollectionFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param DeliveryboyOrderCollF $deliveryboyOrderCollF
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
		\Retailinsights\ProcessCBOOrders\Model\ResourceModel\Reason\CollectionFactory $reasonCollectionFactory,
		 DriverOrderCollF $DriverOrderCollF,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
		$this->reasonCollectionFactory = $reasonCollectionFactory;
        $this->DriverOrderCollF = $DriverOrderCollF;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Initialize data source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
       if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
				$name = $this->getData("name");
				$driverOrderColl = $this->DriverOrderCollF;
				//echo $driverOrderColl->getSelect()->__toString();
				//die;
				$orderId = $item['order_id'];
				$driverOrderColl->addFieldToFilter('order_id', $item['order_id']);
                $driverOrder = $driverOrderColl->getFirstItem();
                //$orderId = $driverOrder->getOrderId();
				$orderStatus = $item['status'];
                $incrementId = $item['increment_id'];
				$deliveryboyId = $driverOrder->getDriverId();

				$reasonCollection = $this->reasonCollectionFactory->create();
				$reasonCollection->addFieldToSelect("reason");
                $reasonCollection->addFieldToFilter("order_increment_id", $incrementId);
				$reasonCollection->addFieldToFilter("driver_id", $deliveryboyId);
                $deliveryboyreason =  $reasonCollection->getFirstItem();
			    $reasonval = $deliveryboyreason->getReason();

                if ($orderStatus == 'dispatched_to_courier' || $orderStatus == 'order_not_delivered') {
                if (empty($reasonval)) {
                        $item[$name]["edit"] = [
                        "href"   => $this->urlBuilder->getUrl(
                            "sales/order/view",
                            [
                                "order_id" => $orderId
                            ]
                        ),
                        "label"  => __("Not Delivered"),
						'target' => '_blank',
                        "hidden" => false
                       ];
					     } else {
						$item[$name]["edit"] = [
                        "href"   => $this->urlBuilder->getUrl(
                            "sales/order/view",
                            [
                                "order_id" => $orderId
                            ]
                        ),
                        "label"  => __("View Reason"),
					    'target' => '_blank',
                        "hidden" => false
                    ];
					      }
				 }

            }
        }
        return $dataSource;
    }
}
