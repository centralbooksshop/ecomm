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
use Webkul\DeliveryBoy\Api\Data\RatingInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Rating extends AbstractModel implements RatingInterface, IdentityInterface
{
    public const STATUS_APPROVED = 1;
    public const STATUS_PENDING = 2;
    public const STATUS_NOT_APPROVED = 3;

    /**
     * Tag to associate cache entries with
     */
    public const CACHE_TAG = "expressdelivery_rating";

    /**
     * Default Id for when id field value is null
     */
    public const NOROUTE_ID = "no-route";
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_rating";
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_rating";

    /**
     * Initialize model object
     *
     * @return self
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\DeliveryBoy\Model\ResourceModel\Rating::class
        );
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
     * Get empty object.
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
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getRating()
    {
        return parent::getData(self::RATING);
    }

    /**
     * @inheritDoc
     */
    public function setRating($rating)
    {
        return $this->setData(self::RATING, $rating);
    }

    /**
     * @inheritDoc
     */
    public function getComment()
    {
        return parent::getData(self::COMMENT);
    }

    /**
     * @inheritDoc
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
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
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
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
     * Get Average ratings.
     *
     * @param int $deliveryboyId
     * @return self
     */
    public function getAverageRating($deliveryboyId)
    {
        return (float)$this->getCollection()->addFieldToFilter(
            "status",
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addFieldToFilter(
            "deliveryboy_id",
            $deliveryboyId
        )->addExpressionFieldToSelect(
            "avg_rating",
            "ROUND(AVG({{rating}}), 1)",
            "rating"
        )->getFirstItem()->getAvgRating();
    }
}
