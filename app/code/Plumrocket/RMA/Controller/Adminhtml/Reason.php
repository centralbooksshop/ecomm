<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml;

class Reason extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::reason';

    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_reason_form_data';

    /**
     * Model of main class
     *
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Reason';

    /**
     * Actibe menu
     *
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::reason';

    /**
     * Object Title
     *
     * @var string
     */
    protected $_objectTitle     = 'Return Reason';

    /**
     * Object titles
     *
     * @var string
     */
    protected $_objectTitles    = 'Return Reasons';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
