<?php 

namespace Lof\PincodeChecker\Block\Adminhtml\Dataimport;

class Importdata extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Lof_PincodeChecker';
        $this->_controller = 'adminhtml_dataimport';
        parent::_construct();
        $this->buttonList->remove('back');
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('reset');

        $this->addButton(
                    'backhome',
                    [
                        'label' => __('Back'),
                        'on_click' => sprintf("location.href = '%s';", $this->getUrl('lof_pincodeChecker/pincodechecker/index/')),
                        'class' => 'back',
                        'level' => -2
                    ]
                );


    }


    public function getHeaderText()
    {
        return __('Import Location Data');
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }


    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('[route_name] / dataimport/save');
    }
}