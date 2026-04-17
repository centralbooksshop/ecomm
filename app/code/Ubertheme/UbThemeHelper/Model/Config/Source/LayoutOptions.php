<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */
namespace Ubertheme\UbThemeHelper\Model\Config\Source;

use \Ubertheme\UbThemeHelper\Helper\Data as Helper;

class LayoutOptions
{
    /**
     * Options builder
     *
     * @return array
     */
    public static function buildOptions($className)
    {
        $options = [];
        $type = null;
        $tmp = explode('\\', $className);
        if ( isset ($tmp[sizeof($tmp)-1]) ) {
            $strClassName = $tmp[sizeof($tmp)-1];
            $type = str_replace('layouts', '', strtolower($strClassName));
        }
        if ( $type ) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $themeId = Helper::getCurrentThemeId();

            /** @var \Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory $themeCollectionFactory */
            $themeCollectionFactory = $om->create('\Magento\Theme\Model\ResourceModel\Theme\Grid\CollectionFactory');
            $collection = $themeCollectionFactory->create();
            $collection->addFieldToFilter('main_table.theme_id', array('eq' => $themeId));
            /** @var \Magento\Theme\Model\Theme $theme */
            $theme = $collection->getFirstItem();
            if ($theme) {
                $rootPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))));
                $layouts = [];

                $parentTheme = $theme->getParentTheme();
                if ($parentTheme) { //level 2
                    $parentTheme2 = $parentTheme->getParentTheme();
                    if ($parentTheme2) { //level 3
                        $realPathToTheme = $rootPath.'/app/design/frontend/'.$parentTheme2->getThemePath();
                        $realPathToLayouts = "{$realPathToTheme}/Ubertheme_UbThemeHelper/layout/";
                        $layouts = array_merge($layouts, self::getLayoutOptions($realPathToLayouts));
                    }
                    $realPathToTheme = $rootPath.'/app/design/frontend/'.$parentTheme->getThemePath();
                    $realPathToLayouts = "{$realPathToTheme}/Ubertheme_UbThemeHelper/layout/";
                    $layouts = array_merge($layouts, self::getLayoutOptions($realPathToLayouts));
                }

                //level 1
                $realPathToTheme = $rootPath.'/app/design/frontend/'.$theme->getThemePath();
                $realPathToLayouts = "{$realPathToTheme}/Ubertheme_UbThemeHelper/layout/";
                $layouts = array_merge($layouts, self::getLayoutOptions($realPathToLayouts));

                $layouts = array_unique($layouts);
                if ($layouts) {
                    foreach ($layouts as $layoutName) {
                        if (preg_match("/({$type}_)/i", $layoutName)) {
                            $options[] = [
                                'value' => $layoutName,
                                'label' => ucfirst(str_replace('_', ' ', $layoutName))
                            ];
                        }
                    }
                }
            }
        }

        return $options;
    }

    protected static function getLayoutOptions($realPathToLayouts) {
        $rs = [];
        $layouts = glob($realPathToLayouts . '*.xml');
        if (is_array($layouts)) {
            foreach ($layouts as $layout) {
                $layoutName = str_replace($realPathToLayouts, '', $layout);
                $layoutName = str_replace('.xml', '', $layoutName);
                $rs[] = $layoutName;
            }
        }

        return $rs;
    }

}
