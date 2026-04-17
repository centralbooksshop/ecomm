<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config edit page
 *
 * @author  Magento Core Team <core@magentocommerce.com>
 */
namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends \Magento\Backend\Block\Widget
{
    const DEFAULT_SECTION_BLOCK = 'Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form';

    /**
     * Form block class name
     *
     * @var string
     */
    protected $_formBlockName;

    /**
     * Block template File
     *
     * @var string
     */
    protected $_template = 'Ubertheme_UbThemeHelper::system/config/edit.phtml';

    /**
     * Configuration structure
     *
     * @var \Ubertheme\UbThemeHelper\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ubertheme\UbThemeHelper\Model\Config\Structure $configStructure
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ubertheme\UbThemeHelper\Model\Config\Structure $configStructure,
        array $data = []
    ) {
        $this->_configStructure = $configStructure;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout object
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */
        $section = $this->_configStructure->getElement($this->getRequest()->getParam('section'));
        $this->_formBlockName = $section->getFrontendModel();
        if (empty($this->_formBlockName)) {
            $this->_formBlockName = self::DEFAULT_SECTION_BLOCK;
        }
        $this->setTitle($section->getLabel());
        $this->setHeaderCss($section->getHeaderCss());

        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'id' => 'save',
                'label' => 'Save Config',
                'title' => 'Save Config',
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#config-edit-form']],
                ]
            ]
        );

        //back button
        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'id' => 'back',
                'label' => __('Back'),
                'class' => 'back secondary',
                'onclick' => "location.href = '".$this->getBackUrl()."'",
                'data_attribute' => [],
            ]
        );
        $block = $this->getLayout()->createBlock($this->_formBlockName);

        $this->setChild('form', $block);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve rendered save buttons
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve config save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/config/save', ['_current' => true]);
    }

    public function getBackUrl()
    {
        $websiteId = $this->getRequest()->getParam('website');
        $storeId = $this->getRequest()->getParam('store');
        $vendor = $this->getRequest()->getParam('vendor');
        $params = [];
        if ($vendor) {
            $params['vendor'] = $vendor;
        }
        if ($websiteId) {
            $params['website'] = $websiteId;
        }
        if ($storeId) {
            $params['store'] = $storeId;
        }

        return $this->getUrl('ubthemehelper/theme/index', $params);
    }

    /**
     * @return string
     */
    public function getConfigSearchParamsJson()
    {
        $params = [];
        if ($this->getRequest()->getParam('section')) {
            $params['section'] = $this->getRequest()->getParam('section');
        }
        if ($this->getRequest()->getParam('group')) {
            $params['group'] = $this->getRequest()->getParam('group');
        }
        if ($this->getRequest()->getParam('field')) {
            $params['field'] = $this->getRequest()->getParam('field');
        }

        return json_encode($params);
    }
}
