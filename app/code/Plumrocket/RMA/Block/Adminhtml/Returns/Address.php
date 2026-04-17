<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

/**
 * Edit returns address form container block
 */
class Address extends \Magento\Backend\Block\Widget\Form\Container
{
    use TemplateTrait;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->templateTraitInit();
        $this->_controller = 'adminhtml_returns';
        $this->_mode = 'address';
        $this->_blockGroup = 'Plumrocket_RMA';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Return Address'));
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Edit Return Address');
    }

    /**
     * Back button url getter
     *
     * @return string
     */
    public function getBackUrl()
    {
        $request = $this->getRequest();

        $params = [];
        if ($parentId = $request->getParam('parent_id')) {
            $params['id'] = $parentId;
        } elseif ($orderId = $request->getParam('order_id')) {
            $params['order_id'] = $orderId;
        }

        return $this->getUrl(Data::SECTION_ID . '/*/edit', $params);
    }
}
