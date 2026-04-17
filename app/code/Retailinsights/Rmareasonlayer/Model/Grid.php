<?php

/**
 * Grid Grid Model.
 * @category  Webkul
 * @package   Retailinsights_Rmareasonlayer
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Retailinsights\Rmareasonlayer\Model;

use Retailinsights\Rmareasonlayer\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'plumrocket_rma_returns_missings';

    /**
     * @var string
     */
    protected $_cacheTag = 'plumrocket_rma_returns_missings';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'plumrocket_rma_returns_missings';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Retailinsights\Rmareasonlayer\Model\ResourceModel\Grid');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set EntityId.
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set Title.
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get getContent.
     *
     * @return varchar
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Set Content.
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Get IsActive.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set IsActive.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

   
}
