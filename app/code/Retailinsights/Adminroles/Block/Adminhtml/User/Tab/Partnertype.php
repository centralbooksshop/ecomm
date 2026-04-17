<?php

namespace Retailinsights\Adminroles\Block\Adminhtml\User\Tab;

/**
 * Order custom tab
 *
 */
class Partnertype extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'partnertype.phtml';
    protected $eavAttribute;
    protected $eavConfig;
    protected $userCollectionFactory;
	protected $collectionFactory;


    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Attribute $eavAttribute,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
		\Centralbooks\DeliveryPartner\Model\ResourceModel\Partner\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->_coreRegistry = $registry;
		$this->collection = $collectionFactory;
        parent::__construct($context, $data);
    }
    public function getAfter()
    {
        return 'roles_section';
    }
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('permissions_user');
    }
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Deliveryboy Partnertype Role');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Deliveryboy Partnertype Role');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    public function getPartnerlist(){
       $storepickupcollm = array();
		$options[] = ['label' => '', 'value' => ''];
		$partner_list_collection = $this->collection->create();
        $partnerlistcoll = $partner_list_collection->getData();
       
        foreach ($partnerlistcoll as $collvalue) {
              $options[] = [
                'value' => $collvalue['partner_id'],
                'label' => $collvalue['name'],
               ];
        }
        return  $options;
		
    }

    public function getAdminuserlist($admin_user_id){
        $adminusercollection = $this->userCollectionFactory->create();
        $partnerlist = array();
        foreach ($adminusercollection as $admin_user_value) {
            if($admin_user_value['user_id'] == $admin_user_id){
                $partnerlist['value'] = $admin_user_value['deliveryboy_partner_type'];
                $partnerlist['username'] = $admin_user_value['username']; 
            }
        }
        return  $partnerlist;
    }
}

?>