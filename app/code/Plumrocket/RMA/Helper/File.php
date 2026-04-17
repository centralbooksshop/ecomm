<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\File\Uploader as FileUploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem\Io\File as FileIo;

class File extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Additional path to file storage
     *
     * @var string
     */
    protected $additionalPath = '';

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var FileIo
     */
    protected $fileIo;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @param Context                $context
     * @param Config                 $configHelper
     * @param FileIo                 $fileIo
     * @param UploaderFactory        $uploaderFactory
     */
    public function __construct(
        Context $context,
        Config $configHelper,
        FileIo $fileIo,
        UploaderFactory $uploaderFactory
    ) {
        $this->configHelper = $configHelper;
        $this->fileIo = $fileIo;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }

    /**
     * Upload file from request
     *
     * @param  string $fileId
     * @return array
     */
    public function upload($fileId)
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->configHelper->getFileAllowedExtensions());
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        // Check file size.
        $size = $uploader->getFileSize();
        if ($size > $this->configHelper->getFileMaxSize(true)) {
            throw new \Exception(__(
                'Sorry, your file is too large. Please, upload file up to %1 MB only.',
                $this->configHelper->getFileMaxSize()
            ), -1);
        }

        return $uploader->save($this->configHelper->getBaseTmpMediaPath());
    }

    /**
     * Create base media directory if not exists
     *
     * @return $this
     */
    public function checkAndCreateBaseFolder()
    {
        $baseMediaPath = $this->configHelper->getBaseMediaPath();
        if (! $this->fileIo->fileExists($baseMediaPath, false)) {
            $this->fileIo->checkAndCreateFolder($baseMediaPath);
            // !! It works only for Apache
            $this->fileIo->write($baseMediaPath . '/.htaccess', 'Deny from all');
        }

        return $this;
    }

    /**
     * Set additional path
     *
     * @param string $path
     * @return $this
     */
    public function setAdditionalPath($path)
    {
        $this->additionalPath = (string)$path;
        return $this;
    }

    /**
     * Get additional path
     *
     * @return string
     */
    public function getAdditionalPath()
    {
        return $this->additionalPath;
    }

    /**
     * Prepare and take message files
     *
     * @param  array $filesTmp
     * @return array
     */
    public function takeMessageFiles($filesTmp)
    {
        $files = [];

        if (is_array($filesTmp)) {
            $ds = DIRECTORY_SEPARATOR;
            $available = $this->configHelper->getFileMaxCount();

            $this->checkAndCreateBaseFolder();

            foreach ($filesTmp as $data) {
                // Need to use basename, because path can contain ".." to navigate to any site files
                $filename = isset($data['filename']) ? $this->basename($data['filename']) : '';
                $file = $this->configHelper->getBaseTmpMediaPath() . $ds . ltrim($filename, $ds);

                // Check max files count.
                if (count($files) >= $available) {
                    $this->fileIo->rm($file);
                    continue;
                }

                if ($filename = $this->take($file)) {
                    $files[] = $filename;
                }
            }
        }

        return $files;
    }

    /**
     * Prepare and take shipping label files
     *
     * @param  array $filesTmp
     * @return string|null
     */
    public function takeShippingLabel($filesTmp)
    {
        $files = [];

        if (is_array($filesTmp)) {
            $ds = DIRECTORY_SEPARATOR;
            $available = $this->configHelper->getShippingLabelCount();

            $this->checkAndCreateBaseFolder();

            foreach ($filesTmp as $data) {
                // Need to use basename, because path can contain ".." to navigate to any site files
                $filename = isset($data['filename']) ? $this->basename($data['filename']) : '';
                $file = $this->configHelper->getBaseTmpMediaPath() . $ds . ltrim($filename, $ds);

                // Check max files count.
                if (count($files) >= $available) {
                    $this->fileIo->rm($file);
                    continue;
                }

                if ($filename = $this->take($file)) {
                    $files[] = $filename;
                }
            }
        }

        return isset($files[0]) ? $files[0] : null;
    }

    /**
     * Take file from tmp directory to stable storage
     *
     * @param  string $file
     * @return string|bool
     */
    public function take($file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $baseMediaPath = $this->configHelper->getBaseMediaPath();
        $allowedExtensions = $this->configHelper->getFileAllowedExtensions();

        $filename = basename($file);
        if (! is_string($filename) || ! trim($filename)) {
            return false;
        }

        if (! $this->fileIo->fileExists($file)) {
            return false;
        }

        // Check extension.
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (! $ext || ! in_array($ext, $allowedExtensions)) {
            $this->fileIo->rm($file);
            return false;
        }

        // Check max size.
        if (filesize($file) > $this->configHelper->getFileMaxSize(true)) {
            $this->fileIo->rm($file);
            return false;
        }

        // If file is exists, generate unique name.
        $fileDest = $baseMediaPath . $ds
            . trim($this->getAdditionalPath(), $ds) . $ds
            . ltrim($filename, $ds);

        $filename = FileUploader::getNewFileName($fileDest);
        // $filename = FileUploader::getDispretionPath($filename) . $ds . $filename;
        $fileDest = $baseMediaPath . $ds
            . trim($this->getAdditionalPath(), $ds) . $ds
            . ltrim($filename, $ds);

        $this->fileIo->checkAndCreateFolder(dirname($fileDest));

        // Move file to stable storage.
        if ($this->fileIo->mv($file, $fileDest)
            && $this->fileIo->fileExists($fileDest)
        ) {
            return $filename;
        }

        return false;
    }

    /**
     * Retrieve file basename
     *
     * @param  string $path
     * @return string
     */
    public function basename($path)
    {
        $name = basename((string)$path);
        if ('.' === $name || '..' === $name) {
            $name = '';
        }

        return $name;
    }
}
