<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Setup;

use Plumrocket\Base\Setup\AbstractUninstall;

class Uninstall extends AbstractUninstall
{

    /**
     * @var string
     */
    protected $_configSectionId = 'prrma';

    /**
     * @var string[]
     */
    protected $_pathes = ['/app/code/Plumrocket/RMA'];

    /**
     * @var string[]
     */
    protected $_tables = [
        'plumrocket_rma_condition',
        'plumrocket_rma_reason',
        'plumrocket_rma_resolution',
        'plumrocket_rma_response_template',
        'plumrocket_rma_return_rule',
        'plumrocket_rma_returns',
        'plumrocket_rma_returns_address',
        'plumrocket_rma_returns_item',
        'plumrocket_rma_returns_message',
        'plumrocket_rma_returns_track',
        'plumrocket_rma_store',
        'plumrocket_rma_text',
    ];
}
