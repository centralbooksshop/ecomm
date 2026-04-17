<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml;

class Response extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::response';

    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_response_form_data';

    /**
     * Model of main class
     *
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Response';

    /**
     * Actibe menu
     *
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::response';

    /**
     * Object Title
     *
     * @var string
     */
    protected $_objectTitle     = 'Quick Response Template';

    /**
     * Object titles
     *
     * @var string
     */
    protected $_objectTitles    = 'Quick Response Templates';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
