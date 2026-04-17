<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml;

class Condition extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::condition';

    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_condition_form_data';

    /**
     * Model of main class
     *
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Condition';

    /**
     * Actibe menu
     *
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::condition';

    /**
     * Object Title
     *
     * @var string
     */
    protected $_objectTitle     = 'Item Condition';

    /**
     * Object titles
     *
     * @var string
     */
    protected $_objectTitles    = 'Item Conditions';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
