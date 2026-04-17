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

use Magento\Framework\Model\AbstractModel;
use Webkul\DeliveryBoy\Api\Data\CommentInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Reason extends AbstractModel implements CommentInterface, IdentityInterface
{
    /**
     * Tag to associate cache entries with
     */
    public const CACHE_TAG = "expressdelivery_deliveryboy";

    /**
     * Default Id for when id field value is null
     */
    public const NOROUTE_ID = "no-route";
    
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_deliveryboy";
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_deliveryboy";

    /**
     * Initialize reason model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\Reason::class);
    }

    /**
     * Load object data
     *
     * @param  int         $id
     * @param  null|string $field
     * @return self
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteDeliveryboy();
        }
        return parent::load($id, $field);
    }

    /**
     * Return empty Instance.
     *
     * @return self
     */
    public function noRouteDeliveryboy()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * Return array of name of object in cache
     *
     * @return string[]
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
    public function getComment()
    {
        return parent::getData(self::REASON);
    }

    /**
     * @inheritDoc
     */
    public function setComment($reason)
    {
        return $this->setData(self::REASON, $reason);
    }

    /**
     * @inheritDoc
     */
    public function getSenderId()
    {
        return parent::getData(self::SENDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSenderId($senderId)
    {
        return $this->setData(self::SENDER_ID, $senderId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get isDeliveryBoy flag  0 false 1 true
     *
     * Whether the reason is written by delivery boy
     *
     * @return int|null
     */
    public function getIsDeliveryboy()
    {
        return parent::getData(self::IS_DELIVERYBOY);
    }

    /**
     * Set isDeliveryBoy flag  0 false 1 true
     *
     * @param int $isDeliveryboy
     * @return self
     */
    public function setIsDeliveryboy($isDeliveryboy)
    {
        return $this->setData(self::IS_DELIVERYBOY, $isDeliveryboy);
    }

    /**
     * @inheritDoc
     */
    public function getOrderIncrementId()
    {
        return parent::getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryboyOrderId()
    {
        return parent::getData(self::DELIVERYBOY_ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryboyOrderId($deliveryboyOrderId)
    {
        return $this->setData(self::DELIVERYBOY_ORDER_ID, $deliveryboyOrderId);
    }
}
