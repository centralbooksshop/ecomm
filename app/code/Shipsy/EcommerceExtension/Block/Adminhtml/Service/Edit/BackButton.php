<?php
namespace Shipsy\EcommerceExtension\Block\Adminhtml\Service\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    private function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    private function getUrl($route = '', $params = [])
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\UrlInterface::class)
            ->getUrl($route, $params);
    }
}
