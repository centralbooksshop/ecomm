<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Pdflabels;

class One extends \Infomodus\Fedexlabel\Controller\Adminhtml\Pdflabels
{
    protected $_conf;
    protected $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Infomodus\Fedexlabel\Helper\Config $conf
    )
    {
        $this->_conf = $conf;
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory);
    }

    public function execute()
    {
        $label_name = $this->getRequest()->getParam('label_name', null);
        if ($label_name !== null) {
            $path_dir = $this->_conf->getBaseDir('media') . 'fedexlabel/label/';
            if (file_exists($path_dir . $label_name)) {
                if (strpos($label_name, ".png") !== false) {
                    $this->_setLabelToPage($path_dir . $label_name);
                }

                if (file_exists($path_dir . str_replace(".png", ".pdf", $label_name))) {
                    $data = file_get_contents($path_dir . str_replace(".png", ".pdf", $label_name));
                    if ($data !== false) {
                        return $this->fileFactory->create(
                            'fedex_shipping_labels.pdf',
                            $data,
                            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                            'application/pdf',
                            strlen($data)
                        );
                    }
                }
            }
        }
    }

    private function _setLabelToPage($label)
    {
        $image = imagecreatefromstring(file_get_contents($label));

        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);

        $pdf2show = new \Zend_Pdf();
        $page = new \Zend_Pdf_Page($xSize, $ySize);

        $image = \Zend_Pdf_Image::imageWithPath($label);
        $page->drawImage($image, 0, 0, $xSize, $ySize);
        $pdf2show->pages[] = $page;

        $file = fopen(str_replace(".png", ".pdf", $label), 'w');
        fwrite($file, $pdf2show->render());
        fclose($file);
    }
}
