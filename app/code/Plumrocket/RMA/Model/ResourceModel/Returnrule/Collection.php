<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model\ResourceModel\Returnrule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plumrocket\RMA\Model\Config\Source\Status;
use Plumrocket\RMA\Model\Returnrule;

/**
 * Return rule collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Returnrule::class, \Plumrocket\RMA\Model\ResourceModel\Returnrule::class);
    }

    /**
     * Add filter for only enabled
     *
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this->addFieldToFilter('status', Status::STATUS_ENABLED);
    }

    /**
     * Add website filter
     *
     * @param int|array $websiteId
     * @return $this
     */
    public function addWebsiteFilter($websiteId)
    {
        if ($websiteId) {
            if (! is_array($websiteId)) {
                $websiteId = [$websiteId];
            }

            $this->addFieldToFilter('website_id', ['finset' => $websiteId]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        /** @var \Plumrocket\RMA\Model\Returnrule $item */
        foreach ($this->_items as $item) {
            $this->unserializeResolutions($item);
            $item->setWebsiteId(explode(',', (string) $item->getWebsiteId()));
        }

        return $this;
    }

    /**
     * Decode resolution.
     *
     * @param \Plumrocket\RMA\Model\Returnrule $item
     * @return void
     */
    private function unserializeResolutions(Returnrule $item): void
    {
        $resolution = json_decode($item->getResolution()) ?: [];
        $_res = [];
        foreach ($resolution as $rid => $value) {
            $_res[$rid] = $value;
        }
        $item->setResolution($_res);
    }
}
