<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Controller\Adminhtml\Sampledata;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Export extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbThemeHelper::export_data';

    protected $_resultJsonFactory;

    protected $_widgetFactory;

    protected $_pageCollection;

    protected $_blockCollection;

    protected $_block;

    /**
     * @param Context $context
     * @param \Magento\Cms\Model\ResourceModel\Page\Collection $pageCollection
     * @param \Magento\Cms\Model\ResourceModel\Block\Collection $blockCollection
     * @param \Magento\Cms\Model\Block $block
     * @param \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection $widgetCollection
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Magento\Cms\Model\ResourceModel\Page\Collection $pageCollection,
        \Magento\Cms\Model\ResourceModel\Block\Collection $blockCollection,
        \Magento\Cms\Model\Block $block,
        \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection $widgetCollection,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        JsonFactory $resultJsonFactory
    )
    {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_widgetFactory = $widgetFactory;
        $this->_pageCollection = $pageCollection;
        $this->_blockCollection = $blockCollection;
        $this->_widgetCollection = $widgetCollection;
        $this->_block = $block;

        parent::__construct($context);
    }

    public function execute()
    {
        $rs = [
            'status' => 'fail',
        ];
        try {
            //export cms pages
            $this->exportPages();
            //export cms blocks
            $this->exportBlocks();
            //export cms widgets
            $this->exportWidgets();

            //return status
            $rs['status'] = 'done';
            $rs['message'] = __('All CMS Pages, CMS Blocks and Widgets was exported to CSV files successfully.');

        } catch (\Exception $e) {
            $rs['message'] = $e->getMessage();
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->_resultJsonFactory->create();
        return $result->setData($rs);
    }

    public function exportPages()
    {
        $path = $this->_getDataSourcePath();

        $list = array(
            array('title', 'page_layout', 'meta_keywords', 'meta_description', 'identifier', 'content_heading', 'content', 'is_active', 'sort_order', 'layout_update_xml', 'custom_theme', 'custom_root_template', 'custom_layout_update_xml', 'custom_theme_from', 'custom_theme_to')
        );

        $this->_pageCollection->addFieldToSelect('*');
        foreach ($this->_pageCollection as $page) {
            $data = [];
            foreach ($list[0] as $attribute) {
                $data[] = $page->getData($attribute);
            }
            $list[] = $data;
        }

        $fp = fopen($path . '/pages.csv', 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        return true;
    }

    public function exportBlocks()
    {
        $path = $this->_getDataSourcePath();

        $list = array(
            array('title', 'identifier', 'content')
        );

        $this->_blockCollection->addFieldToSelect('*');
        foreach ($this->_blockCollection as $block) {
            $data = [];
            foreach ($list[0] as $attribute) {
                $data[] = $block->getData($attribute);
            }
            $list[] = $data;
        }

        $fp = fopen($path . '/blocks.csv', 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        return true;
    }

    public function exportWidgets()
    {
        $path = $this->_getDataSourcePath();

        $list = array(
            array('block_identifier', 'type_code', 'theme_path', 'title', 'page_groups', 'sort_order')
        );

        $widgetCollection = $this->_widgetCollection->addFieldToSelect('*');
        $widgetCollection->join(array('t' => 'theme'), 'main_table.theme_id = t.theme_id', array('theme_path'));

        foreach ($this->_widgetCollection as $widget) {

            $data = [];
            $params = $widget->getWidgetParameters();
            $data['block_identifier'] = isset($params['block_id']) ? $this->_block->load($params['block_id'])->getData('identifier') : null;
            $data['type_code'] = 'cms_static_block';
            $data['theme_path'] = 'frontend/' . $widget->getData('theme_path');
            $data['title'] = $widget->getTitle();
            $widget = $this->_initWidgetInstance($widget);
            $pageGroups = $widget->getPageGroups();
            $tmpPg = [];
            foreach ($pageGroups as $pageGroup) {
                $tmp = [];
                $pg = $pageGroup['page_group'];
                $tmp['page_group'] = $pg;
                $tmp[$pg] = [];
                $tmp[$pg]['for'] = $pageGroup['page_for'];
                $tmp[$pg]['layout_handle'] = $pageGroup['layout_handle'];
                $tmp[$pg]['block'] = $pageGroup['block_reference'];
                $tmpPg[] = $tmp;
            }
            $pageGroups = $tmpPg;
            $data['page_groups'] = serialize($pageGroups);
            $data['sort_order'] = $widget->getData('sort_order');
            $list[] = $data;
        }

        $fp = fopen($path . '/widgets.csv', 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        return true;
    }

    protected function _initWidgetInstance($widget)
    {
        /** @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->_widgetFactory->create();

        $code = 'cms_static_block';
        $instanceId = $widget->getInstanceId();
        if ($instanceId) {
            $widgetInstance->load($instanceId)->setCode($code);
            if (!$widgetInstance->getId()) {
                $this->messageManager->addError(__('Please specify a correct widget.'));
                return false;
            }
        } else {
            // Widget id was not provided on the query-string.  Locate the widget instance
            // type (namespace\classname) based upon the widget code (aka, widget id).
            $themeId = $widget->getThemeId();
            $type = $code != null ? $widgetInstance->getWidgetReference('code', $code, 'type') : null;
            $widgetInstance->setType($type)->setCode($code)->setThemeId($themeId);
        }
        return $widgetInstance;
    }

    private function _getDataSourcePath() {
        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $reader = $this->_objectManager->get('Magento\Framework\Module\Dir\Reader');
        $path = $reader->getModuleDir('', 'Ubertheme_UbThemeHelper').'/fixtures';

        return $path;
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
