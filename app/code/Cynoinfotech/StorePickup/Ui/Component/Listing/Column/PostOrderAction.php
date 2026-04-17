<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Ui\Component\Listing\Column;

class PostOrderAction extends \Magento\Ui\Component\Listing\Columns\Column
{
    
    /**
    * Url path to Edit
    *
    * @var string
    */
    const URL_PATH_EDIT = 'storepickup/storeorder/edit';
    
    /**
    * Url Path to Delete
    *
    * @var string
    */
    const URL_PATH_DELETE = 'storepickup/storeorder/delete';
    
    /**
     * URL Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    
    protected $urlBuilder;
    
    /**
     * construct
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     *
     */
    
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    /**
     * prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
   
    public function prepareDataSource(array $dataSource)
    {
                        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' =>[
                            'href'=> $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' =>__('Edit')
                        ]
                        // 'delete' =>[
                        //     'href' => $this->urlBuilder->getUrl(
                        //         static::URL_PATH_DELETE,
                        //         [
                        //             'entity_id' => $item['entity_id']
                        //         ]
                        //     ),
                        //     'label' =>__('Delete'),
                        //     'confirm' => [
                        //         'title' => __('Delete "${ $.$data.name }"'),
                        //         'message' => __('Are you sure you wan\'t to delete the store "${ $.$data.name }" ?')
                        //     ]
                        // ]
                    ];
                }
            }
        }
        
        return $dataSource;
    }
}
