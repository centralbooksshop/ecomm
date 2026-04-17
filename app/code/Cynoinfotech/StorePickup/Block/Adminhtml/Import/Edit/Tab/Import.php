<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Import\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Import extends Generic implements TabInterface
{
    /**
     * constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    /**
     * Prepare form
     *
     * @return $this
     */

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('csv Information'),
                'class'  => 'fieldset-wide'
            ]
        );
         
        $fieldset->addField(
            'Csv File',
            'file',
            [
                'name'  => 'csvfile',
                'label' => __('csv file'),
                'title' => __('csv file'),
                'required' => true,
                'class'  => 'csvfile',
                'after_element_html'=>'<span><a href="'.$this->getSampleCsvpath().'">Sample csv</a><span>',
            ]
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('CSV');
    }
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    
    public function getSampleCsvpath()
    {
        $samplefiles_url = $this->getViewFileUrl('Cynoinfotech_StorePickup/samplefiles/store-sample.csv');
        return $samplefiles_url;
    }
}
