<?php
declare(strict_types=1);
namespace Centralbooks\ErpApi\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Gst extends AbstractModel implements IdentityInterface
{

   const CACHE_TAG = 'erp_gst';
   
   /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Centralbooks\ErpApi\Model\ResourceModel\Gst::class);
    }

	 public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getGstId()
    {
        return $this->getData(self::GST_ID);
    }

    /**
     * @inheritDoc
     */
    public function setGstId($gstId)
    {
        return $this->setData(self::GST_ID, $gstId);
    }

    
 

 }