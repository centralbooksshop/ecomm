<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Ubertheme\UbContentSlider\Helper\Image as ImageHelper;
use Ubertheme\UbContentSlider\Model\Item\Image as ImageModel;

class ItemImage extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'image';
    const ALT_FIELD = 'title';

    /**
     * image model
     *
     * @var \Ubertheme\UbContentSlider\Model\Item\Image
     */
    protected $imageModel;

    /**
     * @param ImageModel $imageModel
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ImageModel $imageModel,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->imageModel = $imageModel;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            //$baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            foreach ($dataSource['data']['items'] as & $item) {
                $itemObj = new \Magento\Framework\DataObject($item);
                if ($itemObj->getData('content_type') == 'image') {
                    $item[$fieldName . '_src'] = $this->imageModel->getBaseUrl().$itemObj->getImage();
                    $item[$fieldName . '_orig_src'] =  $this->imageModel->getBaseUrl().$itemObj->getImage();
                } elseif ($itemObj->getData('content_type') == \Ubertheme\UbContentSlider\Model\Item::CONTENT_TYPE_YOUTUBE) {
                    if ($itemObj->getData('video_cover')) {
                        $item[$fieldName . '_src'] = $this->imageModel->getBaseUrl().$itemObj->getVideoCover();
                        $item[$fieldName . '_orig_src'] = $this->imageModel->getBaseUrl().$itemObj->getVideoCover();
                    } else {
                        $item[$fieldName . '_src'] = ImageHelper::getYoutubeThumb($itemObj->getData('video_id'));
                        $item[$fieldName . '_orig_src'] =  ImageHelper::getYoutubeThumb(
                            $itemObj->getData('video_id'),
                            'hqdefault'
                        );
                    }
                } elseif ($itemObj->getData('content_type') == \Ubertheme\UbContentSlider\Model\Item::CONTENT_TYPE_VIMEO) {
                    if ($itemObj->getData('video_cover')) {
                        $item[$fieldName . '_src'] = $this->imageModel->getBaseUrl().$itemObj->getVideoCover();
                        $item[$fieldName . '_orig_src'] = $this->imageModel->getBaseUrl().$itemObj->getVideoCover();
                    } else {
                        $item[$fieldName . '_src'] = ImageHelper::getVimeoThumb($itemObj->getData('video_id'));
                        $item[$fieldName . '_orig_src'] =  ImageHelper::getVimeoThumb(
                            $itemObj->getData('video_id'),
                            'thumbnail_large'
                        );
                    }
                }
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $itemObj->getTitle();
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'ubcontentslider/item/edit',
                    ['item_id' => $itemObj->getItemId(), 'store' => $this->context->getRequestParam('store')]
                );
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
