<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml;

abstract class StorePickupOrder extends \Magento\Backend\App\Action
{
    /**
     * StorePickup Factory
     * @var \Monnect\StorePickup\Model\StorePickupOrderFactory
     */
    protected $storepickuporderFactory;
    
    /**
     * Core Registry
     *
     * @var \Magetno\Framework\Registry
     */
    
    protected $coreRegistry;
    
    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * constructor
     *
     * @param \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->storepickuporderFactory    = $storepickuporderFactory;
        $this->coreRegistry            = $coreRegistry;
        $this->resultRedirectFactory   = $resultRedirectFactory;
        parent::__construct($context);
    }
    /**
     * Init StorePickup
     *
     * @return \Cynoinfotech\StorePickup\Model\StorePickupOrder
     */
    protected function _initStorePickupOrder()
    {
        $Id  = (int) $this->getRequest()->getParam('entity_id');
        /** @var \Cynoinfotech\StorePickup\Model\StorePickup $storepickup */
        $storepickuporder   = $this->storepickuporderFactory->create();
        if ($Id) {
            $storepickuporder->load($Id);
        }
        $this->coreRegistry->register('storepickuporder', $storepickuporder);
        return $storepickuporder;
    }
}
