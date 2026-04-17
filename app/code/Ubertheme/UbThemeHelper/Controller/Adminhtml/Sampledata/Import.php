<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Controller\Adminhtml\Sampledata;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Import extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbThemeHelper::import_data';

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \UberTheme\UbThemeHelper\Model\SampleData\Page
     */
    private $page;

    /**
     * @var \UberTheme\UbThemeHelper\Model\SampleData\Block
     */
    private $block;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Ubertheme\UbThemeHelper\Model\SampleData\Page $page
     * @param \Ubertheme\UbThemeHelper\Model\SampleData\Block $block
     * @param \Ubertheme\UbThemeHelper\Model\SampleData\Widget $widget
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Ubertheme\UbThemeHelper\Model\SampleData\Page $page,
        \Ubertheme\UbThemeHelper\Model\SampleData\Block $block,
        \Ubertheme\UbThemeHelper\Model\SampleData\Widget $widget
    )
    {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->page = $page;
        $this->block = $block;
        $this->widget = $widget;

        parent::__construct($context);
    }

    public function execute()
    {
        $rs = [
            'status' => 'fail',
        ];
        try {
            //install cms blocks
            $this->block->install(['Ubertheme_UbThemeHelper::fixtures/blocks.csv']);
            //install cms pages
            $this->page->install(['Ubertheme_UbThemeHelper::fixtures/pages.csv']);
            //install widgets
            $this->widget->install(['Ubertheme_UbThemeHelper::fixtures/widgets.csv']);

            //return status
            $rs['status'] = 'done';
            $rs['message'] = __('Congratulations, All CMS Pages, CMS Blocks and Widgets have been imported successfully.');

        } catch (\Exception $e) {
            $rs['message'] = $e->getMessage();
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->_resultJsonFactory->create();
        return $result->setData($rs);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

}
