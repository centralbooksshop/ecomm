<?php

namespace Retailinsights\Adminroles\Plugin\Block\Adminhtml\User\Edit\Tab;

use Magento\Framework\Option\ArrayInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class Main implements ArrayInterface
{
    protected $eavConfig;
	protected $userCollectionFactory;
	
	 /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
		CollectionFactory $userCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->eavConfig = $eavConfig;
    }
	
	/**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    )
    {
        $form = $subject->getForm();
		$isElementDisabled = false;
        if (is_object($form)) {
            $fieldset = $form->addFieldset('admin_user_school', ['legend' => __('School Selection for Dashboard')]);
            $userId = $subject->getRequest()->getParam('user_id');
			if(isset($userId)) {
				$school_id = $this->getCurrentSchool($userId);
			} else {
			   $school_id = '';
			}

			$fieldset->addField(
            'schoolfilter',
            'multiselect',
            [
                'name' => 'schoolfilter[]',
                'label' => __('School Selection'),
				'class'    => 'school-filter',
                'title' => __('School Selection'),
                'required' => false,
                'values' => $this->getAllSchool(),
				'value'   => $school_id,
                'disabled' => $isElementDisabled
            ]
             );

            $subject->setForm($form);
        }

        return $proceed();
    }

	public function toOptionArray()
    {
		$options = [];
        $options[] = [
            'value' => 0,
            'label' => 'Page 1',
        ];
        $options[] = [
            'value' => 1,
            'label' => 'Page 2',
        ];
		$options[] = [
            'value' => 2,
            'label' => 'Page 3',
        ];
        return $options;
    }

	public function getAllSchool()
	{
        $allschool_attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
        $options = $allschool_attribute->getSource()->getAllOptions();
        return $options;
    }

    public function getCurrentSchool($admin_user_id){
        $usercollection = $this->userCollectionFactory->create();
        $schoolid = array();
        foreach ($usercollection as $user_value) {
             //echo '<pre>'; print_r($user_value->getData()); die;
            if($user_value['user_id'] == $admin_user_id){
                $schoolid = $user_value['school'];
            }
        }
        return  $schoolid;
    }

     
}