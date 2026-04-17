<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml;

class Returnrule extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::returnrule';

    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_return_rule_form_data';

    /**
     * Model of main class
     *
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Returnrule';

    /**
     * Actibe menu
     *
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::returnRule';

    /**
     * Object Title
     *
     * @var string
     */
    protected $_objectTitle     = 'Return Rule';

    /**
     * Object titles
     *
     * @var string
     */
    protected $_objectTitles    = 'Return Rules';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
