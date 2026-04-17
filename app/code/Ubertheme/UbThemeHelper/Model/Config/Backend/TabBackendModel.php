<?php
/**
 * Copyright © 2019 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Backend;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class TabBackendModel extends \Ubertheme\UbThemeHelper\App\Config\Value
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * Serialized constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Json $serializer = null,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $objectManager,$data);
    }

    /**
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            $this->setValue(empty($value) ? false : $this->serializer->unserialize($value));
        }
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        if (is_array($this->getValue())) {
            $value = $this->getValue();

            // Unset array element with '__empty' key
            unset($value['__empty']);
            foreach($value as $key => $item) {
                if(!isset($item['status']))  $value[$key]['status'] = '';
                if ($item['type_default']) {
                    if ($item['type_default'] == 'reviews.tab') {
                        $value[$key]['type'] = 'static_block';
                    } else {
                        $value[$key]['type'] = 'attribute_code';
                    }
                }
                if(!isset($item['static_block']))  $value[$key]['static_block'] = '';
                if(!isset($item['attribute_code']))  $value[$key]['attribute_code'] = '';
            }
            $this->setValue($this->serializer->serialize($value));
        }

        parent::beforeSave();

        return $this;
    }
}
