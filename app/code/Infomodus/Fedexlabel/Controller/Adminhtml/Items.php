<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml;

/**
 * Items controller
 */
abstract class Items extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_handy;
    protected $logger;
    protected $modelFactory;
    protected $order;
    protected $orderRepository;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Infomodus\Fedexlabel\Model\ItemsFactory $modelFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderFactory $order,
        \Infomodus\Fedexlabel\Helper\Handy $handy,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->modelFactory = $modelFactory;
        $this->order = $order;
        $this->_handy = $handy;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Initiate action
     *
     * @return this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Infomodus_Fedexlabel::items')->_addBreadcrumb(__('Labels'), __('Labels'));
        return $this;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_getAclResource());
    }

    protected function _getAclResource()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'index':
                $aclResource = 'Infomodus_Fedexlabel::items';
                break;
            case 'save':
                $aclResource = 'Infomodus_Fedexlabel::create';
                break;
            case 'delete':
                $aclResource = 'Infomodus_Fedexlabel::delete';
                break;
            default:
                $aclResource = 'Infomodus_Fedexlabel::fedexlabel_acl';
                break;
        }
        return $aclResource;
    }
}
