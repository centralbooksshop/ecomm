<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
namespace Infomodus\Fedexlabel\Helper;
class Pdf extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_conf;
    public $labelModel;
    protected $messageManager;

    /**
     * Pdf constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Config $conf
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Infomodus\Fedexlabel\Model\Items $labelModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Infomodus\Fedexlabel\Helper\Config $conf,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Infomodus\Fedexlabel\Model\Items $labelModel
    )
    {
        $this->labelModel = $labelModel;
        $this->_conf = $conf;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
        $this->messageManager = $messageManager;
    }

    public function createManyPDF($labels)
    {
        $pdf = new \Zend_Pdf();
        foreach ($labels as $label) {
            $pdf = $this->createPDF($label, $pdf);
        }

        return $this->fileFactory->create(
            'fedex_shipping_labels.pdf',
            $pdf->render(),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    public function createPDF($label_id, $pdf = null)
    {
        $img_path = $this->_conf->getBaseDir('media') . 'fedexlabel/label/';
        if (is_string($label_id)) {
            $label = $this->labelModel->load($label_id);
        } else {
            $label = $label_id;
        }
        if ($label && file_exists($img_path . $label->getLabelname()) && filesize($img_path . $label->getLabelname()) > 256) {
            if ($label->getTypePrint() == "pdf") {
                $pdf2 = \Zend_Pdf::load($img_path . $label->getLabelname());
                foreach ($pdf2->pages as $k => $page) {
                    $template2 = clone $pdf2->pages[$k];
                    $page2 = new \Zend_Pdf_Page($template2);
                    $pdf->pages[] = $page2;
                }
            }

            if ($label->getTypePrint() == "png") {
                $pdf->pages[] = $this->_setLabelToPage($img_path . $label->getLabelname());
            }

            $label->setRvaPrinted(1);
            $label->save();
        } else {
            $this->messageManager->addErrorMessage(__('To order a ' . $label->getOrderIncrementId() . ' Not Found label ' . $label->getLabelname()));
        }

        return $pdf;
    }

    private function _setLabelToPage($label)
    {
        $image = imagecreatefromstring(file_get_contents($label));

        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);

        $page = new \Zend_Pdf_Page($xSize, $ySize);

        $image = \Zend_Pdf_Image::imageWithPath($label);
        $page->drawImage($image, 0, 0, $xSize, $ySize);

        return $page;
    }
}