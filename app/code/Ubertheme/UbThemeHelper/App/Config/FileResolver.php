<?php
/**
 * Application config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ubertheme\UbThemeHelper\App\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Ubertheme\UbThemeHelper\Framework\Module\Dir\Reader
     */
    protected $_themeReader;

    /**
     * File iterator factory
     *
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * FileResolver constructor.
     * @param \Ubertheme\UbThemeHelper\Framework\Module\Dir\Reader $themeReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(
        \Ubertheme\UbThemeHelper\Framework\Module\Dir\Reader $themeReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
    )
    {
        $this->iteratorFactory = $iteratorFactory;
        $this->filesystem = $filesystem;
        $this->_themeReader = $themeReader;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'global':
                $iterator = $this->_themeReader->getConfigurationFiles($filename);
                break;
            case 'primary':
                $directory = $this->filesystem->getDirectoryRead(DirectoryList::CONFIG);
                $absolutePaths = [];
                foreach ($directory->search('{' . $filename . ',*/' . $filename . '}') as $path) {
                    $absolutePaths[] = $directory->getAbsolutePath($path);
                }
                $iterator = $this->iteratorFactory->create($absolutePaths);
                break;
            default:
                $iterator = $this->_themeReader->getConfigurationFiles($scope . '/' . $filename);
                break;
        }

        return $iterator;
    }
}
