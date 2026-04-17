<?php
namespace Shipsy\EcommerceExtension\Block\Adminhtml\Service\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton implements ButtonProviderInterface
{
    /**
     * Provide configuration for "Save and Continue Edit" button on form
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form'
                    ],
                ],
            ],
            'sort_order' => 80,
        ];
    }
}
