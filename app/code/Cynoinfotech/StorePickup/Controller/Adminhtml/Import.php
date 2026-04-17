<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml;

abstract class Import extends \Magento\Backend\App\Action
{
    
    /**
     * StorePickup Factory
     *
     * @var \Cynoinfotech\StorePickup\Model\StorePickupFactory
     */
    protected $storepickupFactory;
    
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
     * @param \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->storepickupFactory    = $storepickupFactory;
        $this->coreRegistry           = $coreRegistry;
        $this->resultRedirectFactory  = $resultRedirectFactory;
        parent::__construct($context);
    }
   
    /**
     * Init StorePickup
     *
     * @return \Cynoinfotech\StorePickup\Model\StorePickup
     */
    protected function _initStorePickup()
    {
        $Id  = (int) $this->getRequest()->getParam('entity_id');
        /** @var \Cynoinfotech\StorePickup\Model\StorePickup $storepickup */
        $storepickup   = $this->storepickupFactory->create();
        if ($Id) {
            $storepickup->load($Id);
        }
        $this->coreRegistry->register('storepickup', $storepickup);
        return $storepickup;
    }
}
