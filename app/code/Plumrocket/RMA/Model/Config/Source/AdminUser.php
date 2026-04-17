<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Config\Source;

use Magento\Framework\Authorization\Policy\Acl;
use Magento\User\Model\UserFactory;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Controller\Adminhtml\Returns;

class AdminUser extends AbstractSource
{
    /**
     * User Factory
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * ACL
     * @var Acl
     */
    protected $acl;

    /**
     * @param Data        $dataHelper
     * @param UserFactory $userFactory
     * @param Acl         $acl
     */
    public function __construct(
        Data $dataHelper,
        UserFactory $userFactory,
        Acl $acl
    ) {
        $this->userFactory = $userFactory;
        $this->acl = $acl;
        parent::__construct($dataHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $users = $this->userFactory->create()->getCollection();

            $this->options = [];
            foreach ($users as $user) {
                if($user->getRoleName() == 'Customer Care Team') {
					if ($this->acl) {
						if (! $this->acl->isAllowed($user->getAclRole(), 'Plumrocket_RMA::' . Data::SECTION_ID)) {
							continue;
						}
					}

					$this->options[] = [
						'label' => ($user->getFirstname() . ' ' . $user->getLastname()),
						'value' => $user->getId()
					];
				}
            }
        }

        return $this->options;
    }
}
