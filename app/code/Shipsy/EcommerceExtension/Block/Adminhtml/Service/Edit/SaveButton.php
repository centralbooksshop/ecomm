<?php
namespace Shipsy\EcommerceExtension\Block\Adminhtml\Service\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton implements ButtonProviderInterface
{
    /**
     * Provide configuration for "Save" button on form
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Service'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'save'],
                ],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
