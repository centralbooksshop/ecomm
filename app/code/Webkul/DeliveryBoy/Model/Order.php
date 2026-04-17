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
namespace Webkul\DeliveryBoy\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\DeliveryBoy\Api\Data\OrderInterface;

class Order extends AbstractModel implements OrderInterface, IdentityInterface
{
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    public const CACHE_TAG = "expressdelivery_order";
    
    /**
     * Default Id for when id field value is null
     */
    public const NOROUTE_ID = "no-route";

    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_order";
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_order";

    /**
     * Initialize Model object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\Order::class);
    }

    /**
     * @inheritDoc
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteOrder();
        }
        return parent::load($id, $field);
    }

    /**
     * Load object with noroute id data
     *
     * @return self
     */
    public function noRouteOrder()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * Return array of name of object in cache
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getOtp()
    {
        return parent::getData(self::OTP);
    }

    /**
     * @inheritDoc
     */
    public function setOtp($otp)
    {
        return $this->setData(self::OTP, $otp);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getIncrementId()
    {
        return parent::getData(self::INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatus()
    {
        return parent::getData(self::ORDER_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatus($orderStatus)
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    /**
     * @inheritDoc
     */
    public function getAssignStatus()
    {
        return parent::getData(self::ASSIGN_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setAssignStatus($assignStatus)
    {
        return $this->setData(self::ASSIGN_STATUS, $assignStatus);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryboyId()
    {
        return parent::getData(self::DELIVERYBOY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryboyId($deliveryboyId)
    {
        return $this->setData(self::DELIVERYBOY_ID, $deliveryboyId);
    }

    /**
     * @inheritDoc
     */
    public function getAlternateDelivery()
    {
        return parent::getData(self::ALTERNATE_DELIVERY);
    }
    
    /**
     * @inheritDoc
     */
    public function setAlternateDelivery($alternateDelivery)
    {
        return $this->setData(self::ALTERNATE_DELIVERY, $alternateDelivery);
    }

    /**
     * @inheritDoc
     */
    public function getPackageItems()
    {
        return parent::getData(self::PACKAGE_ITEMS);
    }
    
    /**
     * @inheritDoc
     */
    public function setPackageItems($packageItems)
    {
        return $this->setData(self::PACKAGE_ITEMS, $packageItems);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryAmount()
    {
        return parent::getData(self::DELIVERY_AMOUNT);
    }
    
    /**
     * @inheritDoc
     */
    public function setDeliveryAmount($deliveryAmount)
    {
        return $this->setData(self::DELIVERY_AMOUNT, $deliveryAmount);
    }

    /**
     * @inheritDoc
     */
    public function getComments()
    {
        return parent::getData(self::COMMENTS);
    }
    
    /**
     * @inheritDoc
     */
    public function setComments($comments)
    {
        return $this->setData(self::COMMENTS, $comments);
    }
}
