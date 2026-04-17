<?php
namespace Retailinsights\Orders\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class MassUploadForm extends Template
{
    protected $_selectedIds;

    public function __construct(
        Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);

        // Get selected IDs from request
        $selected = $this->getRequest()->getParam('selected', []);

        // Handle both JSON and array formats
        if (is_string($selected)) {
            $selected = json_decode($selected, true);
        }

        $this->_selectedIds = is_array($selected) ? $selected : [];
    }

    /**
     * Return all selected IDs
     */
    public function getSelectedIds()
    {
        return $this->_selectedIds;
    }

    /**
     * Return comma-separated selected IDs (for hidden input)
     */
    public function getSelectedIdsString()
    {
        return implode(',', $this->_selectedIds);
    }

    /**
     * Return form action URL
     */
    public function getFormAction()
    {
        return $this->getUrl('retailinsights_admin/orders/MassUploadSave');
    }
}
