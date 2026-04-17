<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml;

class Resolution extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::resolution';

    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_resolution_form_data';

    /**
     * Model of main class
     *
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Resolution';

    /**
     * Actibe menu
     *
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::resolution';

    /**
     * Object Title
     *
     * @var string
     */
    protected $_objectTitle     = 'Resolution';

    /**
     * Object titles
     *
     * @var string
     */
    protected $_objectTitles    = 'Resolutions';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
