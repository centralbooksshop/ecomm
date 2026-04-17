<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Helper;

use Webkul\DeliveryBoy\Model\Deliveryboy\Source\ApproveStatus;

class DeliveryAutomation extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $addressConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Webkul\DeliveryBoy\Model\OrderLocationFactory
     */
    private $orderLocationF;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    private $deliveryboyHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Model\OrderLocationFactory $orderLocationF
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyCollectionFactory
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Framework\HTTP\Client\Curl $curlClient
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\DeliveryBoy\Model\OrderLocationFactory $orderLocationF,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyCollectionFactory,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Magento\Framework\Url $urlModel,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\HTTP\Client\Curl $curlClient
    ) {
        parent::__construct($context);

        $this->addressConfig = $addressConfig;
        $this->jsonHelper = $jsonHelper;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
        $this->orderLocationF = $orderLocationF;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->deliveryboyCollectionFactory = $deliveryboyCollectionFactory;
        $this->urlModel = $urlModel;
        $this->sessionManager = $sessionManager;
        $this->curlClient = $curlClient;
    }

    /**
     * Is sort deliveryboys by nearest distance.
     *
     * @return bool
     */
    public function isSortDeliveryBoyNearestDistanceEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            ModuleGlobalConstants::XML_PATH_SORT_DELIVERYBOY_BY_NEAREST_DISTANCE
        );
    }

    /**
     * Is auto assign DelvieryboyBy Nearest distance.
     *
     * @return bool
     */
    public function isAutoAssignNearestDeliveryBoyEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            ModuleGlobalConstants::XML_PATH_AUTO_ASSIGN_NEAREST_DELIVERYBOY
        );
    }

    /**
     * Get Distance b/w 2 points.
     *
     * @param array $from
     * @param array $to
     * @param string $radiusUnit
     * @return float
     */
    public function getDistanceBetTwoPoints($from, $to, $radiusUnit = 'km')
    {
        $earthRadius = 6371; // km
        $dLat = ((float)$from['latitude'] - (float)$to['latitude']) * M_PI / 180;
        $dLon = ((float)$from['longitude'] - (float)$to['longitude']) * M_PI / 180;
        $lat1 = (float)$to['latitude'] * M_PI / 180;
        $lat2 = (float)$from['latitude'] * M_PI / 180;
     
        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = $earthRadius * $c;
        if ($radiusUnit == 'mile') {
            $m = $d * 0.621371; //for milles
            return $m;
        }
        return $d;
    }

    /**
     * Sort deliveryboy by distances.
     *
     * @param int $orderId
     * @param DeliveryboyCollection $deliveryboyCollection
     * @return void
     */
    public function sortDeliveryBoyDataWithDistances($orderId, $deliveryboyCollection)
    {
        $orderLocation = $this->orderLocationF->create()
            ->getCollection()->addFieldToFilter('order_id', $orderId)
            ->getFirstItem();
        
        $deliveryLocation = [
            'latitude' => $orderLocation->getLatitude(),
            'longitude' => $orderLocation->getLongitude(),
        ];
        
        $sortedDelBoy = [];
        foreach ($deliveryboyCollection as $deliveryboy) {
            $delBoyCoords = [
                'latitude' => $deliveryboy->getLatitude(),
                'longitude' => $deliveryboy->getLongitude(),
            ];
            $distance = $this->getDistanceBetTwoPoints($delBoyCoords, $deliveryLocation);
            $sortedDelBoy[] = [
                'distance' => $distance,
                'deliveryboy' => $deliveryboy,
            ];
        }
        usort($sortedDelBoy, function ($a, $b) {
            if ($a['distance'] < $b['distance']) {
                return -1;
            }
            if ($a['distance'] > $b['distance']) {
                return 1;
            }
            if ($a['distance'] == $b['distance']) {
                return 0;
            }
        });
        return $sortedDelBoy;
    }

    /**
     * Get Address coordinates.
     *
     * @param Address $address
     * @return void
     */
    public function getAddressCoordinates($address)
    {
        try {
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            $addrStr = strip_tags($renderer->renderArray($address));
            $prepAddr = str_replace(' ', '+', $addrStr);
            $googleMapApiKey = $this->deliveryboyHelper->getGoogleMapKey();
            $geocode = $this->fileDriver->fileGetContents(
                'https://maps.google.com/maps/api/geocode/json?key=' . $googleMapApiKey .
                '&address=' . $prepAddr . '&sensor=false'
            );
            $output = $this->jsonHelper->jsonDecode($geocode);
            if (isset($output['results'][0])) {
                $resultData['latitude'] = $output['results'][0]['geometry']['location']['lat'];
                $resultData['longitude'] = $output['results'][0]['geometry']['location']['lng'];
            } else {
                $resultData = null;
                $resultData['latitude'] = "28.6568196";
                $resultData['longitude'] = "77.4182632";
                $output = $this->jsonHelper->jsonEncode($output);
                $this->logger->debug($output);
            }
        } catch (\Throwable $e) {
            $this->logger->debug(__CLASS__);
            $this->logger->debug($e->getMessage());
            $resultData = null;
        }

        return $resultData;
    }

    /**
     * Get Distance Unit.
     *
     * @return void
     */
    public function getDistanceUnit()
    {
        return 'km';
    }

    /**
     * Format deliveryboy name.
     *
     * @param string $deliveryboyName
     * @param float $distance
     * @param string $distanceUnit
     * @return string
     */
    public function formatDeliveryboyName($deliveryboyName, $distance, $distanceUnit = null)
    {
        $formattedDistance = $this->formatDistance($distance, $distanceUnit);
        $formattedName = $deliveryboyName . ' (' . $formattedDistance . ')';
        return $formattedName;
    }

    /**
     * Format distance.
     *
     * @param float $distance
     * @param string $distanceUnit
     * @return string
     */
    public function formatDistance($distance, $distanceUnit = null)
    {
        $distanceUnit = $distanceUnit ?: $this->getDistanceUnit();
        $distance = $this->formatDistanceWithoutUnit($distance);
        $distance = $distance . ' ' . $distanceUnit;
        return $distance;
    }

    /**
     * Format and round distance.
     *
     * @param float $distance
     * @return string
     */
    public function formatDistanceWithoutUnit($distance)
    {
        return number_format($distance, 2, '.', '');
    }

    /**
     * Get Available deliveryboy list.
     *
     * @param DeliveryboyOrder $order
     * @return array
     */
    public function getAvailableDeliveryboyList($order)
    {
        $deliveryboyCollection = $this->deliveryboyCollectionFactory->create();

        $deliveryboyCollection->addFieldToFilter("status", 1)
            ->addFieldToFilter("approve_status", ApproveStatus::STATUS_APPROVED)
            ->addFieldToFilter("availability_status", 1)
            ->setOrder("created_at", "ASC");
        $this->_eventManager->dispatch(
            'wk_deliveryboy_deliveryboy_collection_apply_filter_event',
            [
                'deliveryboy_collection' => $deliveryboyCollection,
                'collection_table_name' => 'main_table',
            ]
        );
        $deliveryboyList = [];
        if ($this->isSortDeliveryBoyNearestDistanceEnabled()) {
            $orderId = $order->getId();
            $nearestSortedDeliveryBoy = $this->sortDeliveryBoyDataWithDistances(
                $orderId,
                $deliveryboyCollection
            );

            $distanceUnit = $this->getDistanceUnit();
            foreach ($nearestSortedDeliveryBoy as $delboy) {
                $eachDeliveryboy = [];
                $eachDeliveryboy["id"] = $delboy['deliveryboy']->getId();
                $name = $delboy["deliveryboy"]->getName();
                $distance = $delboy['distance'];
                $eachDeliveryboy['name'] =
                $this->formatDeliveryboyName($name, $distance);
                $eachDeliveryboy["status"] = $delboy['deliveryboy']->getStatus();
                $eachDeliveryboy[
                    "availabilityStatus"
                ] = (bool)$delboy['deliveryboy']->getAvailabilityStatus();
                $deliveryboyList[] = $eachDeliveryboy;
            }
        } else {
            foreach ($deliveryboyCollection as $each) {
                $eachDeliveryboy = [];
                $eachDeliveryboy["id"] = $each->getId();
                $eachDeliveryboy["name"] = $each->getName();
                $eachDeliveryboy["status"] = $each->getStatus();
                $eachDeliveryboy[
                    "availabilityStatus"
                ] = (bool)$each->getAvailabilityStatus();
                $deliveryboyList[] = $eachDeliveryboy;
            }
        }
        return $deliveryboyList;
    }

    /**
     * Auto assign deliveryboy on order placed.
     *
     * @param Order $order
     * @param AuthenticationHelper $authenticationHelper
     * @return void
     */
    public function autoAssignDeliveryboyOnOrderPlace($order, $authenticationHelper)
    {
        try {
            if (!$this->isAutoAssignNearestDeliveryBoyEnabled()) {
                return;
            }
            $deliveryboys = $this->getAvailableDeliveryboyList($order);
            
            if (empty($deliveryboys)) {
                return;
            }
            $firstDeliveryboy = array_shift($deliveryboys);
            $data = $this->getAssignOrderApiParams($order, $firstDeliveryboy);
            $url = $this->urlModel->getUrl('expressdelivery/api_admin/assignorder', [
                '_query' => $data
            ]);
            $authKey = $authenticationHelper->getCurrentAuthKey();
            $headers = [
                'authKey:'.$authKey,
                'Content-Type:application/x-www-form-urlencoded',
                'Deliveryboy-Automation:'. random_int(1, PHP_INT_SIZE),
            ];
            $this->curlClient->setOption(CURLOPT_URL, $url);
            $this->curlClient->setOption(CURLOPT_POST, true);
            $this->curlClient->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curlClient->setOption(CURLOPT_SSL_VERIFYPEER, $url);
            $this->curlClient->setOption(CURLOPT_HTTPHEADER, $headers);
            
            $this->curlClient->post($url, []);
            $result = $this->curlClient->getBody();
            $this->logger->debug($result);
        } catch (\Throwable $e) {
            $this->logger->debug($e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assignOrderApiParameters.
     *
     * @param Order $order
     * @param array $deliveyrboyArr
     * @return array
     */
    public function getAssignOrderApiParams($order, $deliveyrboyArr)
    {
        return [
            'storeId' => $order->getStoreId(),
            'incrementId' => $order->getIncrementId(),
            'deliveryboyId' => $deliveyrboyArr['id'],
            'adminCustomerEmail' => $this->deliveryboyHelper->getAdminEmail(),
        ];
    }
}
