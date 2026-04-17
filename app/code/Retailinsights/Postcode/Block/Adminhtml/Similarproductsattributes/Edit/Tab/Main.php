<?php

namespace Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Edit\Tab;

/**
 * Similarproductsattributes edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Retailinsights\Postcode\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Retailinsights\Postcode\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Retailinsights\Postcode\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('similarproductsattributes');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		

        // $fieldset->addField(
        //     'att_name',
        //     'select',
        //     [
        //         'label' => __('Attributes_Name'),
        //         'title' => __('Attributes_Name'),
        //         'name' => 'att_name',
				
        //         'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2(),
        //         'disabled' => $isElementDisabled
        //     ]
        // );


        $fieldset->addField(
            'postcode',
            'text',
            [
                'label' => __('postcode'),
                'title' => __('postcode'),
                'name' => 'postcode',
                
                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'is_shippable',
            'select',
            [
                'label' => __('is_shippable'),
                'title' => __('is_shippable'),
                'name' => 'is_shippable',
                
                'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'cod_availability',
            'select',
            [
                'label' => __('cod_availability'),
                'title' => __('cod_availability'),
                'name' => 'cod_availability',
                
                'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2(),
                'disabled' => $isElementDisabled
            ]
        );


						

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
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

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
