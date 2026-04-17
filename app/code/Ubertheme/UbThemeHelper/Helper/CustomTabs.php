<?php
/**
 * Copyright © 2019 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Helper;

use Magento\Framework\Serialize\Serializer\Json;

class CustomTabs extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LIST_CUSTOM_TAB = 'product_view/ub_custom_tab/tab_content/items';

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $helperOutput;

    /**
     * @var \Ubertheme\UbThemeHelper\Helper\Data
     */
    protected $helperData;

    /**
     * CustomTabs constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Json $serializer
     * @param \Magento\Catalog\Helper\Output $helperOutput
     * @param Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Json $serializer,
        \Magento\Catalog\Helper\Output $helperOutput,
        Data $helperData
    )
    {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->helperOutput = $helperOutput;
        $this->helperData = $helperData;
    }

    /**
     * @param $product
     * @param $item
     * @return bool
     */
    public function canShowTab($product, $item) {
        $rs = false;
        if (!$item['status']) {
            return $rs;
        }
        if ($item['category_ids'] == '' && $item['product_ids'] == '') {
            $rs = true;
        } else {
            if ($item['product_ids']) {
                $productId = $product->getId();
                $productIds = explode(',', $item['product_ids']);
                if (in_array($productId, $productIds)){
                    $rs = true;
                }
            }
            if ($item['category_ids']) {
                $categoriesIds = explode(',', $item['category_ids']);
                $productCatIds = $product->getCategoryIds();
                foreach ($categoriesIds as $key => $cateId) {
                    if (in_array($cateId, $productCatIds)) {
                        $rs = true;
                        break;
                    }
                }
            }

        }

        return $rs;
    }

    /**
     * @param $product
     * @param $key
     * @param $item
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemTabContent($product, $key, $item) {
        $data = [];
        if ($item['type'] == 'static_block') {
            $data['alias'] = $key;
            $data['title'] = $item['title'];
            $data['type'] = $item['type_default'];
            $data['html'] = $this->helperData->getBlockHTML($item['static_block']);
        } else {
            $data['alias'] = $key;
            $data['title'] = $item['title'];
            $data['type'] = $item['type_default'];
            $attribute = $product->getResource()->getAttribute($item['attribute_code']);
            if ($attribute) {
                $content = $attribute->getFrontend()->getValue($product);
                if (!empty($content)) {
                    $data['html'] = $this->helperOutput->productAttribute($product, $content, $item['attribute_code']);
                } else {
                    $data['html'] = '';
                }
            } else {
                $data['html'] =  '';
            }
        }

        return $data;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEnabledTabs($product) {
        $arrTabs = [];
        $tabSettings = $this->getTabSettings();
        if ($tabSettings) {
            foreach ($tabSettings as $key => $item) {
                if (!$this->canShowTab($product, $item)) {
                    continue;
                }
                $arrTabs[] = $this->getItemTabContent($product, $key, $item);
            }
        }

        return $arrTabs;
    }

    public function getTabSettings() {
        $settings = [];
        $tabSettings = $this->helperData->getParam(self::LIST_CUSTOM_TAB);
        if ($tabSettings) {
            $settings = $this->serializer->unserialize($tabSettings);
        }

        return $settings;
    }

    public function isActivated($tabCode) {
        $rs = null;
        $tabSettings = $this->getTabSettings();
        if ($tabSettings) {
            foreach ($tabSettings as $key => $setting) {
                if (isset($setting['type_default']) && $setting['type_default'] == $tabCode) {
                    $rs = $setting['status'];
                    break;
                }
            }
        }

        return ($rs == 'on') ? true : false;
    }

}
