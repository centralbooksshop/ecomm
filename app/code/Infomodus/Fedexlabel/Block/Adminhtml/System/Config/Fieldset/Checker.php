<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Fedexlabel\Block\Adminhtml\System\Config\Fieldset;

use Infomodus\Fedexlabel\Model\Config\Defaultaddress;
use Infomodus\Fedexlabel\Model\Config\Defaultdimensionsset;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Fieldset renderer which expanded by default
 */
class Checker extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var Defaultaddress
     */
    private $defaultaddress;
    /**
     * @var ScopeConfigInterface
     */
    private $storesConfig;
    /**
     * @var Defaultdimensionsset
     */
    private $defaultdimensionsset;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        Defaultaddress $defaultaddress,
        Defaultdimensionsset $defaultdimensionsset,
        array $data = []
    )
    {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->defaultaddress = $defaultaddress;
        $this->storesConfig = $context->getScopeConfig();
        $this->defaultdimensionsset = $defaultdimensionsset;
    }

    /**
     * Whether is collapsed by default
     *
     * @var bool
     */
    protected $isCollapsedDefault = true;

    protected function _getHeaderCommentHtml($element)
    {
        $errors = [];
        if (!class_exists('\DVDoug\BoxPacker\Packer', true)) {
            $errors[] = '<div style="color: red;">Box Packer is not installed. Run the command "composer require dvdoug/boxpacker:^2.6" from the site root directory</div>';
        }

        if (count($this->defaultaddress->toOptionArray()) <= 1) {
            $errors[] = '<div style="color: red;">Add at least one address here Sales - FedEx Addresses</div>';
        }

        if (count($this->defaultdimensionsset->toOptionArray()) == 0) {
            $errors[] = '<div style="color: red;">Add at least one dimension box here Sales - FedEx Boxes</div>';
        }

        if ($this->storesConfig->getValue('fedexlabel/shipping/defaultshipper') == '') {
            $errors[] = '<div style="color: red;">Select address for Default Shipper Address in the "Default Shipping Settings" block</div>';
        }

        if ($this->storesConfig->getValue('fedexlabel/credentials/meter_number') == ''
            || $this->storesConfig->getValue('fedexlabel/credentials/userid') == ''
            || $this->storesConfig->getValue('fedexlabel/credentials/password') == ''
            || $this->storesConfig->getValue('fedexlabel/credentials/shippernumber') == ''
        ) {
            $errors[] = '<div style="color: red;">Fill correct credentials fro FedEx account in the "Your FedEx Account Credentials" block</div>';
        }

        if ($this->storesConfig->getValue('fedexlabel/weightdimension/defweigth') == '') {
            $errors[] = '<div style="color: red;">Fill correct default weight of your products in the "Weight and Dimensions" block</div>';
        }

        if (!empty($errors)) {
            return implode("<br>", $errors);
        }

        return __('Configuration filled successfully');
    }
}
