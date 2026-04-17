<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Block\Adminhtml;

/**
 * Adminhtml ubcsl slides content block
 */
class Slide extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_slide';
        $this->_blockGroup = 'Ubertheme_UbContentSlider';
        $this->_headerText = __('Manage Sliders');

        parent::_construct();

        if ($this->_isAllowedAction('Ubertheme_UbContentSlider::slide_save')) {
            $this->buttonList->update('add', 'label', __('Add New Slider'));
        } else {
            $this->buttonList->remove('add');
        }
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
}
