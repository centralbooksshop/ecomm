<?php

namespace Infomodus\Fedexlabel\Model\Config;

use Magento\Framework\Filesystem;

/**
 * System config image field backend model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class ImageUploader extends \Magento\Config\Model\Config\Backend\Image
{
    const UPLOAD_DIR = 'fedexlabel';
    private $_conf;
    private $fedexModel;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Infomodus\Fedexlabel\Model\Fedex $fedexModel,
        \Infomodus\Fedexlabel\Helper\Config $conf,
        array $data = []
    )
    {
        $this->_conf = $conf;
        $this->fedexModel = $fedexModel;
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
    }

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * Makes a decision about whether to add info about the scope.
     *
     * @return boolean
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['gif', 'png'];
    }

    public function afterSave()
    {
        $storeId = $this->_conf->getRequest()->getParam('store', null);
        $code = $this->_conf->getStoreByCode($storeId);
        if ($code) {
            $storeId = $code->getId();
        }

        $value = $this->getValue();
        $isValueChanged = $this->isValueChanged();

        if (!empty($value) && $isValueChanged) {
            $uploader = $this->fedexModel;
            $uploader->meterNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/meter_number', $storeId);
            $uploader->UserID = $this->_conf->getStoreConfig('fedexlabel/credentials/userid', $storeId);
            $uploader->Password = $this->_conf->getStoreConfig('fedexlabel/credentials/password', $storeId);
            $uploader->shipperNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/shippernumber', $storeId);
            $uploader->testing = $this->_conf->getStoreConfig('fedexlabel/testmode/testing', $storeId);
            $uploader->uploadImage($this->_getUploadDir() . '/../' . $value);
        }

        return parent::afterSave();
    }
}
