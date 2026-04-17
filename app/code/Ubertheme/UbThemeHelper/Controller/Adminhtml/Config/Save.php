<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Controller\Adminhtml\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbThemeHelper::save_settings';

    /** @var  \Ubertheme\UbThemeHelper\Model\ConfigFactory */
    protected $_configFactory;

    /** @var  \Magento\Framework\Stdlib\StringUtils */
    protected $_stringHelper;

    /** @var  \Ubertheme\UbThemeHelper\App\Config */
    protected $_themeConfig;

    /** @var \Ubertheme\UbThemeHelper\Model\Config\Structure */
    protected $_configStructure;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList */
    protected $_directoryList;

    /** @var \Magento\Backend\Model\Auth */
    protected $_auth;

    protected $configGroups = null;

    protected $configs = [];

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ubertheme\UbThemeHelper\Model\Config\Structure $configStructure
     * @param \Ubertheme\UbThemeHelper\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param \Ubertheme\UbThemeHelper\App\Config $themeConfig
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ubertheme\UbThemeHelper\Model\Config\Structure $configStructure,
        \Ubertheme\UbThemeHelper\Model\ConfigFactory $configFactory,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Ubertheme\UbThemeHelper\App\Config $themeConfig,
        DirectoryList $directoryList
    )
    {
        parent::__construct($context);
        $this->_configFactory = $configFactory;
        $this->_objectManager = $context->getObjectManager();
        $this->_stringHelper = $stringHelper;
        $this->_themeConfig = $themeConfig;
        $this->_directoryList = $directoryList;
        $this->_configStructure = $configStructure;
        $this->_auth = $context->getAuth();
    }

    /**
     * Save configuration
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $vendor = $this->getRequest()->getParam('vendor');
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            $themeId = $this->getRequest()->getParam('themeId');

            if (!$this->configGroups) {
                $this->configGroups = $this->_getGroupsForSave();
                $this->configs = [];
            }

            $configData = [
                'section' => $section,
                'website' => $website,
                'store' => $store,
                'theme' => $themeId,
                'groups' => $this->configGroups,
            ];
            /** @var \Ubertheme\UbThemeHelper\Model\Config $configModel */
            $configModel = $this->_configFactory->create(['data' => $configData]);

            //save config data
            $configModel->save();

            /** @var \Magento\Theme\Model\Theme $theme */
            $theme = $this->_objectManager->get('Magento\Theme\Model\Theme')->load($themeId);
            //$themePath = str_replace('_', '/', $theme->getThemePath());
            $themePath = $theme->getThemePath();

            $appPath = $this->_directoryList->getPath('app');

            if ($this->configGroups) {
                //update to less variables in needed config section
                $this->_updateLessVars("{$appPath}/design/frontend/{$themePath}/web/css/source/variables/_{$section}.less");
                //update to less vars used for lessjs
                $this->_updateLessVars("{$appPath}/design/frontend/{$themePath}/web/js/less-vars.js", true);
            }

            //handle custom CSS code
            if (preg_match("/custom_css/", $section)) {
                $this->_writeCustomCSS("{$appPath}/design/frontend/{$themePath}/web/css/custom/custom.css");
            }

            $this->messageManager->addSuccess(__('Your changes have been saved successfully.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }
        $this->_saveState($this->getRequest()->getPost('config_state'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'ubthemehelper/config/edit',
            [
                'vendor' => $vendor,
                'themeId' => $themeId,
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true
            ]
        );
    }

    protected function _updateLessVars($pathToLessFile, $useForLessJS = false)
    {
        $section = $this->getRequest()->getParam('section');
        if (file_exists($pathToLessFile)) {
            if ($this->configGroups) {
                //process config data
                foreach ($this->configGroups as $groupId => $groupData) {
                    if (isset($groupData['groups']) && $groupData['groups']) {
                        foreach ($groupData['groups'] as $group2Id => $group2Data) {
                            if (isset($group2Data['fields'])) {
                                $this->_buildConfigs($section, $group2Data, $group2Id, $groupId);
                            }
                        }
                    } else if (isset($groupData['fields']) && $groupData['fields']) {
                        $this->_buildConfigs($section, $groupData, $groupId, 0);
                    }
                }
                //write config values to needed file
                if (sizeof($this->configs)) {
                    //open file for write
                    $errorMsg = __("Can't open the file for writing.");
                    $file = fopen($pathToLessFile, 'w') or die($errorMsg);
                    if ($useForLessJS) {
                        $lessJSVars = null;
                        $tmp = [];
                        foreach ($this->configs as $key => $value) {
                            //we only write to less file variables with value is not null
                            if ($value != null) {
                                $tmp[] = "'" . $key . "':'" . $value . "'";
                            }
                        }
                        $lessJSVars = "var ubLessVars = {\n" . implode(",\n", $tmp) . "\n};";
                        fwrite($file, $lessJSVars);
                    } else {
                        foreach ($this->configs as $key => $value) {
                            //we only write to less file variables with value is not null
                            if ($value != null) {
                                fwrite($file, $key . ":" . $value . ";\n");
                            }
                        }
                    }
                    //close file
                    fclose($file);
                }
            }
        }

        return true;
    }

    protected function _buildConfigs($sectionId, $groupData, $groupId, $groupParentId = 0)
    {
        /** @var  \Ubertheme\UbThemeHelper\Helper\Data $ubHelper */
        $ubHelper = $this->_objectManager->get('Ubertheme\UbThemeHelper\Helper\Data');
        $mediaFileRoot = $ubHelper->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."ubertheme/ubthemehelper";
        foreach ($groupData['fields'] as $name => $data) {
            if (!empty($groupParentId)) {
                $configPath = "{$sectionId}/{$groupParentId}/{$groupId}/{$name}";
            } else {
                $configPath = "{$sectionId}/{$groupId}/{$name}";
            }
            //check has delete
            if (isset($data['value']['delete']) AND $data['value']['delete']) {
                /** @var \Magento\Framework\Filesystem $fileSystem */
                $fileSystem = $this->_objectManager->get('Magento\Framework\Filesystem');
                if (!empty($groupParentId)) {
                    $fileURL = "ubertheme/ubthemehelper/{$configPath}/{$data['value']['value']}";
                } else {
                    $fileURL = "ubertheme/ubthemehelper/{$configPath}/{$data['value']['value']}";
                }
                $filePath = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($fileURL);
                //remove media file
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            //check is used as variables
            if (preg_match("/_var/", $name)) {
                if (!isset($data['value'])) {
                    $data['value'] = null;
                }
                if ($data['value'] == null) {
                    //get default value
                    $data['value'] = $this->_themeConfig->getValue($configPath);
                }
                if (preg_match("/bg_image_var/", $name)) {
                    //if has deleted
                    if (isset($data['value']['delete']) AND $data['value']['delete']) {
                        unset($data['value']['value']);
                    }
                    if (!isset($data['value']['tmp_name']) || empty($data['value']['tmp_name'])) {
                        if (!isset($data['value']['value'])) {
                            $path = "{$mediaFileRoot}/{$configPath}/blank_image.png";
                        } else {
                            $path = "{$mediaFileRoot}/{$configPath}/" . $data['value']['value'];
                        }
                    } else {
                        $path = "{$mediaFileRoot}/{$configPath}/" . $this->_themeConfig->getValue($configPath);
                    }
                    $path = preg_replace("/index.php\//", "", $path);
                    $this->configs["@{$name}"] = "~\"{$path}\"";
                } else {
                    if (preg_match("/\\s/", $data['value'])) {
                        $this->configs["@{$name}"] = "~\"{$data['value']}\"";
                    } else {
                        $this->configs["@{$name}"] = "{$data['value']}";
                    }
                }
            }
        }

        return true;
    }

    protected function _writeCustomCSS($pathToCustomCSSFile)
    {
        if ($this->configGroups) {
            foreach ($this->configGroups as $groupId => $groupData) {
                foreach ($groupData['fields'] as $name => $field) {
                    if (preg_match("/css_code/", $name)) {
                        @file_put_contents($pathToCustomCSSFile, $field['value']);
                    }
                }
            }
        }
    }

    protected function _getGroupsForSave()
    {
        $groups = $this->getRequest()->getPost('groups');
        $files = $this->getRequest()->getFiles('groups');

        if ($files && is_array($files)) {
            /**
             * Carefully merge $_FILES and $_POST information
             * None of '+=' or 'array_merge_recursive' can do this correct
             */
            foreach ($files as $groupName => $group) {
                $data = $this->_processNestedGroups($group);
                if (!empty($data)) {
                    if (!empty($groups[$groupName])) {
                        $groups[$groupName] = array_merge_recursive((array)$groups[$groupName], $data);
                    } else {
                        $groups[$groupName] = $data;
                    }
                }
            }
        }

        return $groups;
    }

    /**
     * Process nested groups
     *
     * @param mixed $group
     * @return array
     */
    protected function _processNestedGroups($group)
    {
        $data = [];

        if (isset($group['fields']) && is_array($group['fields'])) {
            foreach ($group['fields'] as $fieldName => $field) {
                if (!empty($field['value'])) {
                    $data['fields'][$fieldName] = ['value' => $field['value']];
                }
            }
        }

        if (isset($group['groups']) && is_array($group['groups'])) {
            foreach ($group['groups'] as $groupName => $groupData) {
                $nestedGroup = $this->_processNestedGroups($groupData);
                if (!empty($nestedGroup)) {
                    $data['groups'][$groupName] = $nestedGroup;
                }
            }
        }

        return $data;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Save state of configuration field sets
     *
     * @param array $configState
     * @return bool
     */
    protected function _saveState($configState = [])
    {
        if (is_array($configState)) {
            $configState = $this->sanitizeConfigState($configState);
            $adminUser = $this->_auth->getUser();
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = [];
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = [];
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }

        return true;
    }

    /**
     * Sanitize config state data
     *
     * @param array $configState
     * @return array
     */
    protected function sanitizeConfigState($configState)
    {
        $sectionList = $this->_configStructure->getSectionList();
        $sanitizedConfigState = $configState;
        foreach ($configState as $sectionId => $value) {
            if (array_key_exists($sectionId, $sectionList)) {
                $sanitizedConfigState[$sectionId] = (bool)$sanitizedConfigState[$sectionId] ? '1' : '0';
            } else {
                if ($value == '1') {
                    $sanitizedConfigState[$sectionId] = (bool)$sanitizedConfigState[$sectionId] ? '1' : '0';
                } else {
                    unset($sanitizedConfigState[$sectionId]);
                }
            }
        }

        return $sanitizedConfigState;
    }
}
