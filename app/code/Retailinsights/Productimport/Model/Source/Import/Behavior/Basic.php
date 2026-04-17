<?php

namespace Retailinsights\Productimport\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;

class Basic extends \Magento\ImportExport\Model\Source\Import\Behavior\Basic
{
   
	 /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Add/Update'),
            //Import::BEHAVIOR_REPLACE => __('Replace'),
            Import::BEHAVIOR_DELETE => __('Delete')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return 'catalog_product';
    }
}