<?php
/**
 * Module configuration file reader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ubertheme\UbThemeHelper\Framework\Module\Dir;

use Magento\Framework\Config\FileIterator;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Component\ComponentRegistrar;

class Reader
{
    /**
     * Module directories that were set explicitly
     *
     * @var array
     */
    protected $customModuleDirs = [];

    /**
     * Directory registry
     *
     * @var Dir
     */
    protected $moduleDirs;

    /**
     * Modules configuration provider
     *
     * @var ModuleListInterface
     */
    protected $modulesList;

    /**
     * @var FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * @var Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @param \Magento\Theme\Model\Theme $theme
     * @param ComponentRegistrar $componentRegistrar
     * @param Dir $moduleDirs
     * @param ModuleListInterface $moduleList
     * @param FileIteratorFactory $fileIteratorFactory
     * @param Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(
        \Magento\Theme\Model\Theme $theme,
        ComponentRegistrar $componentRegistrar,
        Dir $moduleDirs,
        ModuleListInterface $moduleList,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->moduleDirs = $moduleDirs;
        $this->modulesList = $moduleList;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->readFactory = $readFactory;
        $this->theme = $theme;
        //for back-end context
        $this->themeId = \Ubertheme\UbThemeHelper\Helper\Data::getCurrentThemeId();
        if (!$this->themeId) {
            //for front-end context
            $this->themeId = $scopeConfig->getValue('design/theme/theme_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    /**
     * Go through all themes and find corresponding files of active themes
     *
     * @param string $filename
     * @param string $subDir
     * @return array
     */
    private function getThemeDir($theme)
    {
        return $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $theme);
    }

    /**
     * Go through all themes and find configuration files of active themes
     *
     * @param string $filename
     * @return FileIterator
     */
    public function getConfigurationFiles($filename)
    {
        $configFiles = [];
        $theme = $this->theme->load($this->themeId);

        if ($filename != 'adminhtml/settings.xml') {
            $parentTheme = $theme->getParentTheme();
            if ($parentTheme) { //level 2
                if ($parentTheme->getParentTheme()) { //level 3
                    $configFiles[] = $this->getFile($filename, $parentTheme->getParentTheme()->getFullPath());
                }
                $configFiles[] = $this->getFile($filename, $parentTheme->getFullPath());
            }
        }

        $themePath = $theme->getFullPath(); //level 1
        $configFiles[] = $this->getFile($filename, $themePath);

        $configFiles = array_filter($configFiles);

        return $this->fileIteratorFactory->create($configFiles);
    }

    /**
     * Get theme's file
     *
     * @param $filename
     * @param string $themePath
     * @return string|null
     */
    private function getFile($filename, $themePath = '')
    {
        $result = null;
        $themeEtcDir = $this->getThemeDir($themePath) . '/etc';
        $file = $themeEtcDir . '/' . $filename;
        $directoryRead = $this->readFactory->create($themeEtcDir);
        $path = $directoryRead->getRelativePath($file);
        if ($directoryRead->isExist($path)) {
            $result = $file;
        }

        return $result;
    }

    /**
     * Go through all themes and find composer.json files of active themes
     *
     * @return FileIterator
     */
    public function getComposerJsonFiles()
    {
        $filePath = $this->getFile('composer.json');
        return $this->fileIteratorFactory->create([$filePath]);
    }

    /**
     * Retrieve list of theme action files
     *
     * @return array
     */
    public function getActionFiles()
    {
        $actions = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            $actionDir = $this->getModuleDir(Dir::MODULE_CONTROLLER_DIR, $moduleName);
            if (!file_exists($actionDir)) {
                continue;
            }
            $dirIterator = new \RecursiveDirectoryIterator($actionDir, \RecursiveDirectoryIterator::SKIP_DOTS);
            $recursiveIterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::LEAVES_ONLY);
            $namespace = str_replace('_', '\\', $moduleName);
            /** @var \SplFileInfo $actionFile */
            foreach ($recursiveIterator as $actionFile) {
                $actionName = str_replace('/', '\\', str_replace($actionDir, '', $actionFile->getPathname()));
                $action = $namespace . "\\" . Dir::MODULE_CONTROLLER_DIR . substr($actionName, 0, -4);
                $actions[strtolower($action)] = $action;
            }
        }

        return $actions;
    }

    /**
     * Get module directory by directory type
     *
     * @param string $type
     * @param string $moduleName
     * @return string
     */
    public function getModuleDir($type, $moduleName)
    {
        if (isset($this->customModuleDirs[$moduleName][$type])) {
            return $this->customModuleDirs[$moduleName][$type];
        }
        return $this->moduleDirs->getDir($moduleName, $type);
    }

    /**
     * Set path to the corresponding module directory
     *
     * @param string $moduleName
     * @param string $type directory type (etc, controllers, locale etc)
     * @param string $path
     * @return void
     */
    public function setModuleDir($moduleName, $type, $path)
    {
        $this->customModuleDirs[$moduleName][$type] = $path;
    }

}
