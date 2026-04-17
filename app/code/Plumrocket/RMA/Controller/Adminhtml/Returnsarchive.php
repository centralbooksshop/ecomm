<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml;

class Returnsarchive extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::returnsarchive';

    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_returnsarchive_form_data';

    /**
     * Model of main class
     *
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Returns';

    /**
     * Actibe menu
     *
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::returnsarchive';

    /**
     * Object Title
     *
     * @var string
     */
    protected $_objectTitle     = 'Return';

    /**
     * Object titles
     *
     * @var string
     */
    protected $_objectTitles    = 'Returns Archive';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
