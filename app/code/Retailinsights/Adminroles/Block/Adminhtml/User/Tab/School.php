<?php

namespace Retailinsights\Adminroles\Block\Adminhtml\User\Tab;

/**
 * Order custom tab
 *
 */
class School extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'school.phtml';
    protected $eavAttribute;
    protected $eavConfig;
     protected $userCollectionFactory;


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
        array $data = []
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->_coreRegistry = $registry;
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
        return __('School Role');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('School Role');
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
    public function getSchool(){
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
        $options = $attribute->getSource()->getAllOptions();
        return $options;
    }

    public function school($id){
        $collection = $this->userCollectionFactory->create();
        $school = array();
        foreach ($collection as $value) {
            if($value['user_id'] == $id){
                $school['value'] = $value['school'];
                $school['label'] = 'name'; 
            }
        }
        return  $school;
    }
}

?>