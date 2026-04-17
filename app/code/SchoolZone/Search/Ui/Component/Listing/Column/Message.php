<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SchoolZone\Search\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Message extends Column {

    protected $_productFactory;
    protected $_urlBuilder;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => $items) {
                $msg = $dataSource['data']['items'][$key]['message'];
				if(!empty($msg)) {
                $string = (strlen($msg) > 20) ? substr($msg,0,20).'...' : $msg;
                $newMessage = '<div title="'.$msg.'">'.$string.'</div>';
                $dataSource['data']['items'][$key]['message'] = $newMessage;
				} else {
					$dataSource['data']['items'][$key]['message'] = '';
				}
            }
        }
        return $dataSource;
    }
}