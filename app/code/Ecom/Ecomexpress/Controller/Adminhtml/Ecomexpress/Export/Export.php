<?php

namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Export;

use Magento\Framework\App\ResponseInterface;
use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
/**
 * Class Export
 */
class Export extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
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
	 * Export ecomexpress awb in csv format
	 *
	 * @return ResponseInterface
	 */
	public function execute()
	{
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$fileName = "awb.csv";
			$content = $this->_objectManager->create('Ecom\Ecomexpress\Model\Awb')->getCollection()->addFieldToSelect("awb_id")->addFieldToSelect("awb")->addFieldToSelect("shipment_id")->addFieldToSelect("shipment_to")->addFieldToSelect("state")->addFieldToSelect("status")->addFieldToSelect("orderid")->addFieldToSelect("awb_type")->getData();
			$exportheader = array("Awb id", "Awb", "Shipment#", "Shipment To", "State", "Status", "Order#", "Awb Type");
			array_unshift($content, $exportheader);
			$fileexport = $this->_objectManager->create('Magento\Framework\File\Csv');
			$fileexport->saveData("$fileName", $content);
			$this->messageManager->addSuccess(__('AWB Exported Successfully'));
			$this->_redirect('ecomexpress/ecomexpress/awb');	
		}
	}
}