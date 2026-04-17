<?php


namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Export;

use Magento\Framework\App\ResponseInterface;
use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
/**
 * Class Exportpincodes
 */
class Exportpincodes extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
	/**
	 * @var \Magento\Framework\App\Response\Http\FileFactory
	 */
	protected $_fileFactory;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Config\Model\Config\Structure $configStructure
	 * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
	 * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 */
	public function __construct(
			\Magento\Backend\App\Action\Context $context,
			\Magento\Config\Model\Config\Structure $configStructure,
			ConfigSectionChecker $sectionChecker,
			\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
			\Magento\Store\Model\StoreManagerInterface $storeManager
			) {
				$this->_storeManager = $storeManager;
				$this->_fileFactory = $fileFactory;
				parent::__construct($context, $configStructure, $sectionChecker);
	}

	/**
	 * Export ecomexpress pincodes in csv format
	 *
	 * @return ResponseInterface
	 */
	public function execute()
	{
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$fileName = "pincodes.csv";
			$content = $this->_objectManager->create('Ecom\Ecomexpress\Model\Pincode')->getCollection()->addFieldToSelect("pincode_id")->addFieldToSelect("pincode")->addFieldToSelect("city")->addFieldToSelect("state")->addFieldToSelect("state_code")->addFieldToSelect("city_code")->addFieldToSelect("dccode")->addFieldToSelect("created_at")->getData();
			$exportheader = array("Pincode id", "Pincode", "City", "State", "State Code", "City Code", "DCcode", "Created Date & Time");
			array_unshift($content, $exportheader);
			$fileexport = $this->_objectManager->create('Magento\Framework\File\Csv');
			$fileexport->saveData("$fileName", $content);
			$this->messageManager->addSuccess(__('Pincode Exported Successfully'));
			$this->_redirect('ecomexpress/ecomexpress/pincode');
		}
	}
}