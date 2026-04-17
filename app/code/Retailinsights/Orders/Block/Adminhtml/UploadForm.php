<?php
namespace Retailinsights\Orders\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class UploadForm extends Template
{
    protected $_itemId;

    public function __construct(
        Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_itemId = $this->getRequest()->getParam('item_id');
    }

    public function getItemId()
    {
        return $this->_itemId;
    }

    public function getFormAction()
    {
        return $this->getUrl('retailinsights_admin/orders/UploadSave');
    }
}
