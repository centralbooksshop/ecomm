<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */
namespace Ubertheme\UbThemeHelper\Model\Config\Source;

use \Ubertheme\UbThemeHelper\Helper\Data as Helper;

class TemplateOptions
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
            $type = str_replace('templates', '', strtolower($strClassName));
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
                $templates = [];

                $parentTheme = $theme->getParentTheme();
                if ($parentTheme) { //level 2
                    $parentTheme2 = $parentTheme->getParentTheme();
                    if ($parentTheme2) { //level 3
                        $realPathToTheme = $rootPath.'/app/design/frontend/'.$parentTheme2->getThemePath();
                        $realPathToTemplates = "{$realPathToTheme}/Ubertheme_UbThemeHelper/templates/{$type}/";
                        $templates = array_merge($templates, self::getTemplateOptions($realPathToTemplates));
                    }
                    $realPathToTheme = $rootPath.'/app/design/frontend/'.$parentTheme->getThemePath();
                    $realPathToTemplates = "{$realPathToTheme}/Ubertheme_UbThemeHelper/templates/{$type}/";
                    $templates = array_merge($templates, self::getTemplateOptions($realPathToTemplates));
                }
                //level 1
                $realPathToTheme = $rootPath.'/app/design/frontend/'.$theme->getThemePath();
                $realPathToTemplates = "{$realPathToTheme}/Ubertheme_UbThemeHelper/templates/{$type}/";
                $templates = array_merge($templates, self::getTemplateOptions($realPathToTemplates));

                if ($templates) {
                    foreach ($templates as $templateName) {
                        $options[] = [
                            'value' => $templateName,
                            'label' => ucfirst(str_replace('_', ' ', $templateName))
                        ];
                    }
                }
            }
        }

        return $options;
    }

    protected static function getTemplateOptions($realPathToTemplates) {
        $rs = [];
        $templates = glob($realPathToTemplates . '*.phtml');
        if (is_array($templates)) {
            foreach ($templates as $template) {
                $templateName = str_replace($realPathToTemplates, '', $template);
                $templateName = str_replace('.phtml', '', $templateName);
                $rs[] = $templateName;
            }
        }

        return $rs;
    }

}
