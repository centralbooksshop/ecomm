<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\DeliveryBoy\Block\Adminhtml\Template\Grid\Renderer;

class CloseAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Renderer for "Action" column in Newsletter templates grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        $actions[] = [
            'url' => $this->getUrl(
                'expressdelivery/ordertransaction/close',
                [
                    'id' => $row->getId(),
                    'order_id' => $row->getOrderId(),
                ]
            ),
            'caption' => __('Close'),
        ];

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
