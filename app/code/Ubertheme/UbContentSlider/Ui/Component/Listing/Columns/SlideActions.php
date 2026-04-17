<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class SlideActions
 */
class SlideActions extends Column
{
    /** Url path */
    const UBCS_URL_PATH_EDIT = 'ubcontentslider/slide/edit';
    const UBCS_URL_PATH_DELETE = 'ubcontentslider/slide/delete';
    const UBCS_URL_PATH_LIST_ITEMS = 'ubcontentslider/item/index';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::UBCS_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['slide_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['slide_id' => $item['slide_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::UBCS_URL_PATH_DELETE, ['slide_id' => $item['slide_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.title }'),
                            'message' => __('Are you sure you wan\'t to delete the ${ $.$data.title }?  All it\'s child items will be deleted too.')
                        ]
                    ];
                    $item[$name]['listitem'] = [
                        'href' => $this->urlBuilder->getUrl(self::UBCS_URL_PATH_LIST_ITEMS, ['slide_id' => $item['slide_id']]),
                        'label' => __('Manage Slide Items')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
