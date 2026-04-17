<?php
namespace Retailinsights\DtdcCustom\Block\Adminhtml;

class ProcessDtdcOrder extends \Magento\Framework\View\Element\Template
{
	protected $_postFactory;
	public $_storeManager;
	protected $_customerSession;
	private $collectionFactory;

	public function __construct(
		 \Magento\Customer\Model\Session $session,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Backend\Block\Widget\Context $context
		// \Retailinsights\Autodrivers\Model\ListautodriversFactory $collectionFactory
	)
	{
		$this->_customerSession = $session;
		$this->_storeManager=$storeManager;
		// $this->collectionFactory = $collectionFactory;
		parent::__construct($context);
	}

	// public function getAutoDrivers()
	// {
	// 	$driverCollection = $this->collectionFactory->create(); 
    //     $filter = $driverCollection->getCollection();

	// 	$driverFilterData =  $filter->getData();
	// 	return $driverFilterData;
	// }
	
}
?>