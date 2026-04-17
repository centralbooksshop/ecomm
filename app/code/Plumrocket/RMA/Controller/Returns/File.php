<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Magento\Framework\File\Mime;

class File extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->getModel();

        $storage = $this->getRequest()->getParam('storage');
        if (! $model->getId() || ! is_string($storage) || ! trim($storage)) {
            $this->_forward('noroute');
            return;
        }

        // Need to use basename, because path can contain ".." to navigate to any site files
        $name = $this->fileHelper->basename($storage);

        $fileFullPath = $this->configHelper->getBaseMediaPath() . DIRECTORY_SEPARATOR
            . $model->getId() . DIRECTORY_SEPARATOR
            . $name;
        $resultRaw = $this->resultRawFactory->create();

        try {
            $contentType = (new Mime())->getMimeType($fileFullPath);
            $resultRaw->setHeader('Content-Disposition', 'inline; filename="' . $name . '"', true)
                ->setHeader('Content-Type', $contentType);
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }

        return $resultRaw->setContents($this->fileDriver->fileGetContents($fileFullPath));
    }

    /**
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        if ($this->specialAccess()) {
            return true;
        }

        return parent::canViewReturn();
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client cannot have separate order on this page
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function specialAccess()
    {
        // Access by code for admin.
        $model = $this->getModel();
        $request = $this->getRequest();
        $code = $this->returnsHelper->getCode($model, ReturnsHelper::CODE_SALT_FILE);
        if ($request->getParam('code')
            && $request->getParam('code') === $code
        ) {
            return true;
        }

        return false;
    }
}
