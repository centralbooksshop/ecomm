<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Fedexlabel\Controller\Pdf;

class One extends \Infomodus\Fedexlabel\Controller\Pdf
{
    public function execute()
    {
        $label_name = $this->getRequest()->getParam('label_name', null);
        if ($label_name !== null) {
            $path_dir = $this->_conf->getBaseDir('media') . 'fedexlabel/label/';
            $data = \file_get_contents($path_dir . $label_name);
            if ($data !== false) {
                return $this->fileFactory->create(
                    'fedex_shipping_labels.pdf',
                    $data,
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }

            $this->resultRedirectFactory->create()->setUrl($this->_buildUrl('*/*/'));
        }

        $this->resultRedirectFactory->create()->setUrl($this->_buildUrl('*/*/'));
    }
}
