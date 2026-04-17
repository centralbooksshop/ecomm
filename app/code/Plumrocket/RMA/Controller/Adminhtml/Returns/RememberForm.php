<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class RememberForm extends Action implements HttpPostActionInterface
{

    public const ADMIN_RESOURCE = 'Plumrocket_RMA::returns';

    /**
     * @var \Plumrocket\RMA\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\RMA\Helper\Data         $dataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\RMA\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
    }

    /**
     * Store form data into session via ajax
     *
     * @return void
     */
    public function execute()
    {
        $this->dataHelper->setFormData();
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['ok']);
    }
}
