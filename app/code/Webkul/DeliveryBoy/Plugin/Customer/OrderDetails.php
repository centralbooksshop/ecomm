<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_MPDeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Plugin\Customer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\ObjectManager;

class OrderDetails
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var \Webkul\DeliveryBoy\Model\Rating
     */
    protected $rating;

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderCollectionFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param ResultFactory $resultFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Webkul\DeliveryBoy\Model\Rating $rating
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderCollectionFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Psr\Log\LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        ResultFactory $resultFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Webkul\DeliveryBoy\Model\Rating $rating,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderCollectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Psr\Log\LoggerInterface $logger,
        ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->order = $order;
        $this->rating = $rating;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->customerFactory = $customerFactory;
        try {
            $this->baseDir = $dir->getPath("media");
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->baseDir = '';
        }
        $this->request = $request;
        $this->resource = $resource;
        $this->deliveryboy = $deliveryboy;
        $this->resultFactory = $resultFactory;
        $this->deliveryboyOrderCollectionFactory = $deliveryboyOrderCollectionFactory;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->om = ObjectManager::getInstance();
    }

    /**
     * Attach deliveryboy details to ordetails api.
     *
     * @param \Webkul\MobikulApi\Controller\Customer\OrderDetails $subject
     * @param Json $response
     * @return Json
     */
    public function afterExecute(
        \Webkul\MobikulApi\Controller\Customer\OrderDetails $subject,
        $response
    ) {
        if (!$this->moduleManager->isOutputEnabled('Webkul_MobikulApi')) {
            return $response;
        }
        if ($this->moduleManager->isOutputEnabled('Webkul_MPDeliveryBoy')) {
            return $response;
        }
        $helper = $this->om->get(\Webkul\MobikulCore\Helper\Data::class);
        $helperCatalog = $this->om->get(\Webkul\MobikulCore\Helper\Catalog::class);
        $adminEmail = $this->deliveryboyHelper->getAdminEmail();
        $websiteId = $this->request->getParam('websiteId', 1);
        $customer = $this->customerFactory->create()
            ->setWebsiteId($websiteId)->loadByEmail($adminEmail);
        $adminId = $customer->getId();
        $returnArray = json_decode($response->getRawData(), true);
        $allowedShipping = explode(
            ",",
            $helper->getConfigData(
                "deliveryboy/configuration/allowed_shipping"
            )
        );
        $incrementId = $this->request->getParam('incrementId', null);
        $customerToken = $this->request->getParam('customerToken', null);
        $customerId = $helper->getCustomerByToken($customerToken);
        
        $returnArray['deliveryBoys'] = [];
        $order = $this->order->loadByIncrementId($incrementId);
        $availableTypes = $this->deliveryboy->getAvailableTypes();
        if (in_array($order->getShippingMethod(), $allowedShipping)) {
            $connection = $this->resource->getConnection();
            $tableName  = $this->resource->getTableName("deliveryboy_deliveryboy");
            $deliveryboyRatingTable  = $this->resource->getTableName("deliveryboy_rating");
            $collection = $this->deliveryboyOrderCollectionFactory->create()
                ->addFieldToFilter("increment_id", $incrementId);
            $collection->getSelect()
                ->reset(\Magento\Framework\DB\Select::COLUMNS);
            $deliveryboyParams = [
                "name" =>            "deliveryboy.name",
                "email" =>           "deliveryboy.email",
                "avatar" =>          "deliveryboy.image",
                "vehicleType"=>      "deliveryboy.vehicle_type",
                "mobileNumber"=>     "deliveryboy.mobile_number",
                "vehicleNumber"=>    "deliveryboy.vehicle_number",
                "id" =>              "deliveryboy.id",
                "deliveryBoyLat" =>  "deliveryboy.latitude",
                "deliveryBoyLong" => "deliveryboy.longitude",
                "picked" =>          "main_table.picked",
                "otp" =>             "main_table.otp",
                "orderStatus" =>     "main_table.order_status",
            ];

            $collection->getSelect()
                ->joinleft(
                    [
                        "deliveryboy" => $tableName
                    ],
                    "main_table.deliveryboy_id=deliveryboy.id",
                    $deliveryboyParams
                );
            foreach ($collection as $item) {
                $deliveryboyDetails = $item->getData();
                $orderItems = $order->getItems();
                $orderVisibleItems = $this->getVisibleOrderItems($orderItems);
                $deliveryboyDetails['products'] =
                    $this->getOrderItemsNameArray($orderVisibleItems);
                $deliveryboyDetails['isEligibleForDeliveryBoy'] = true;
                $Iconheight = $IconWidth = 144;
                $newUrl = "";
                $basePath = $this->baseDir . DIRECTORY_SEPARATOR . $deliveryboyDetails['avatar'];
                try {
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir . DIRECTORY_SEPARATOR;
                        $newPath .= "deliveryboyresized" . DIRECTORY_SEPARATOR . $IconWidth . "x";
                        $newPath .= $Iconheight . DIRECTORY_SEPARATOR . $deliveryboyDetails['avatar'];
                        $helperCatalog->resizeNCache(
                            $basePath,
                            $newPath,
                            $IconWidth,
                            $Iconheight
                        );
                        $newUrl = $helper->getUrl("media");
                        $newUrl .= "deliveryboyresized" . DIRECTORY_SEPARATOR . $IconWidth . "x";
                        $newUrl .= $Iconheight . DIRECTORY_SEPARATOR . $deliveryboyDetails['avatar'];
                    }
                } catch (\Throwable $t) {
                    $this->logger->debug($t->getMessage());
                }
                $deliveryboyDetails['avatar'] = $newUrl;
                $deliveryboyDetails['customerId'] = $customerId;
                $deliveryboyDetails['sellerId'] = $adminId;
                $deliveryboyDetails['picked'] = (bool)$deliveryboyDetails['picked'];
                $deliveryboyDetails['rating'] =
                    $this->rating->getAverageRating($deliveryboyDetails['id']);
                $returnArray['deliveryBoys'][] = $deliveryboyDetails;
                $returnArray['adminAddress'] = $helper->getConfigData(
                    "deliveryboy/configuration/warehouse_address"
                );

            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($returnArray);
        return $resultJson;
    }

    /**
     * Get order items name array.
     *
     * @param array $orderItems
     * @return string[]
     */
    private function getOrderItemsNameArray($orderItems)
    {
        $orderItemsNameArray = [];

        foreach ($orderItems as $orderItem) {
            $orderItemsNameArray[] = $this->getOrderItemReadableName($orderItem);
        }

        return $orderItemsNameArray;
    }

    /**
     * Format Order Item name.
     *
     * @param Magento\Sales\Model\Order\Item $orderItem
     * @return string
     */
    private function getOrderItemReadableName($orderItem)
    {
        return $this->getOrderItemName($orderItem) .
                ' (x' . (int)$orderItem->getQtyOrdered() . ')';
    }

    /**
     * Get all visible items.
     *
     * @param array $orderItems
     * @return array
     */
    private function getVisibleOrderItems($orderItems)
    {
        $orderItemsArray = [];
        foreach ($orderItems as $orderItem) {
            if (empty($orderItem->getParentItemId())) {
                $orderItemsArray[] = $orderItem;
            }
        }

        return $orderItemsArray;
    }

    /**
     * Get Order Item full name.
     *
     * @param array $orderItem
     * @return string
     */
    private function getOrderItemName($orderItem)
    {
        $name = $orderItem->getName();
        $options = $orderItem->getProductOptions();
        $name = empty($options['simple_name']) ? $name : $options['simple_name'];

        return $name;
    }
}
