<?php

namespace Retailinsights\Autodrivers\Block\Adminhtml\Listautodrivers\Edit\Tab;

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
    // protected $_status;

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
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
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
        $model = $this->_coreRegistry->registry('Listautodrivers');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Auto Driver Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		
        $fieldset->addField(
            'driver_name',
            'text',
            [
                'label' => __('Driver name'),
                'title' => __('driver_name'),
                'name' => 'driver_name',
                
                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'driver_mobile',
            'text',
            [
                'label' => __('Mobile Number'),
                'title' => __('driver_mobile'),
                'name' => 'driver_mobile',
                
                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'auto_number',
            'text',
            [
                'label' => __('Auto number'),
                'title' => __('auto_number'),
                'name' => 'auto_number',
                
                // 'options' => \Retailinsights\Postcode\Block\Adminhtml\Similarproductsattributes\Grid::getOptionArray2(),
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
        return __('Auto Drivers');
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
