<?php

namespace Webkul\DeliveryBoy\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;

class Pod extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * Initialize data source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            $target = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $fieldName = $this->getData("name");
	    foreach ($dataSource["data"]["items"] as &$item) {
		 if (isset($item["pod_image_path"]) && !empty($item["pod_image_path"])) {
                    
                    $imageUrl = $target . $item["pod_image_path"];	
                    $item[$fieldName . "_html"] = "<img src='" . $target . $item["pod_image_path"] . "'/>";
		    $item[$fieldName . "_src"] = $imageUrl;
		    $item[$fieldName . '_alt'] = "POD For Order ID # ". $item["increment_id"];
		     $item[$fieldName . '_orig_src'] = $imageUrl;
                }
            }
        }
        return $dataSource;
    }
}

