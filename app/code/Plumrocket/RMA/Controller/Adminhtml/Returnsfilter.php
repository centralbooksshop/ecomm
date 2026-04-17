<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml;

class Returnsfilter extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::returnsfilter';

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
    protected $_activeMenu     = 'Plumrocket_RMA::returnsfilter';

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
    protected $_objectTitles    = 'Returns Filter';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
