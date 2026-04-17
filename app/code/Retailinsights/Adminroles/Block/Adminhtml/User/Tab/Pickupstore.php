<?php

namespace Retailinsights\Adminroles\Block\Adminhtml\User\Tab;

/**
 * Order custom tab
 *
 */
class Pickupstore extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'pickupstore.phtml';
    protected $eavAttribute;
    protected $eavConfig;
    protected $userCollectionFactory;
	protected $storepickupFactory;


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
		\Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        array $data = []
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->_coreRegistry = $registry;
		$this->storepickup = $storepickupFactory;
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
        return __('Pickupstore Role');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Pickupstore Role');
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
    public function getPickupstore(){
       $storepickupcollm = array();
		$options[] = ['label' => '', 'value' => ''];
		$storepickup = $this->storepickup->create();
        $storepickupcoll = $storepickup->getCollection();
       
        foreach ($storepickupcoll as $collvalue) {
              $options[] = [
                'value' => $collvalue['entity_id'],
                'label' => $collvalue['name'],
               ];
        }
        return  $options;
		
    }

    public function pickupstore($id){
        $collection = $this->userCollectionFactory->create();
        $pickupstore = array();
        foreach ($collection as $value) {
            if($value['user_id'] == $id){
                $pickupstore['value'] = $value['pickupstore'];
                $pickupstore['label'] = 'name'; 
            }
        }
        return  $pickupstore;
    }
}

?>