<?php
namespace Centralbooks\ClickpostExtension\Model;

class Awb extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{


	const CACHE_TAG = 'clickpost_waybill';

    protected $_cacheTag = 'clickpost_waybill';

    protected $_eventPrefix = 'clickpost_waybill';

    protected function _construct()
    {
        $this->_init(\Centralbooks\ClickpostExtension\Model\ResourceModel\Awb::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

}