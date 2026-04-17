<?php
namespace Shipsy\EcommerceExtension\Block\Adminhtml\Service\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    public function getButtonData()
    {
        $entityId = $this->request->getParam('entity_id');

        if (!$entityId) {
            return [];
        }

        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => sprintf(
                "deleteConfirm('%s', '%s')",
                __('Are you sure you want to delete this service?'),
                $this->getDeleteUrl($entityId)
            ),
            'sort_order' => 20,
        ];
    }

    private function getDeleteUrl($entityId)
    {
        return $this->urlBuilder->getUrl('softdatasync/service/delete', ['entity_id' => $entityId]);
    }
}
