<?php
namespace MageArray\AddressAutoComplete\Block;

class Googlejs extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MageArray\AddressAutoComplete\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
    }

    public function getApiKey()
    {
        $api = $this->_dataHelper->getGoogleApi();
        return $api;
    }
}
