<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Block\Adminhtml\Resolution\Edit;

use Magento\Framework\Data\Form\Element\Text;

/**
 * @since 2.3.2
 */
class Notice extends Text
{
    public function getElementHtml(): string
    {
        return '<div class="message waring">' .
            __(
                'To make Resolutions available for customers, you need to specify their Period. You can set the' .
                ' Resolution Period in Plumrocket -> RMA -> Return Rules. Please check the' .
                ' <a href="%1" target="_blank">Developer Guide</a> for more instructions.',
                'https://plumrocket.com/docs/magento-rma/v2/devguide/return-resolutions'
            ) .
            '</div>';
    }
}
