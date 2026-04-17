<?php

namespace Centralbooks\AdminDataMask\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Authorization\Model\RoleFactory;
use Centralbooks\AdminDataMask\Helper\Mask;

class GridMaskPlugin
{
    public const ROLE_ADMIN = 'Administrators';

    public const ROLE_ONLINE = 'Online Team';
    public const ROLE_OPERATIONS = 'Operations Team';
    public const ROLE_OUTWARD = 'Outward Team';
    public const ROLE_SUPPORT = 'Customer Care Team';
    public const ROLE_CS_SUPPORT = 'CS Team';
    public const ROLE_MH_GST = 'Maharashtra GST';
    public const ROLE_TG_GST = 'Telangana GST';

    private $authSession;
    private $roleFactory;
    private $mask;

    public function __construct(
        Session $authSession,
        RoleFactory $roleFactory,
        Mask $mask
    ) {
        $this->authSession = $authSession;
        $this->roleFactory = $roleFactory;
        $this->mask = $mask;
    }

    private function getRoleType()
    {
        $user = $this->authSession->getUser();

        if (!$user) {
            return null;
        }

        foreach ($user->getRoles() as $roleId) {
            $role = $this->roleFactory->create()->load($roleId);
            return $role->getRoleName();
        }

        return null;
    }

    public function afterPrepareDataSource($subject, $result)
    {
        if (!isset($result['data']['items'])) {
            return $result;
        }

        $role = $this->getRoleType();

        /**
         * Do NOT mask anything for Administrator
         */
        if ($role === self::ROLE_ADMIN) {
            return $result;
        }

        foreach ($result['data']['items'] as &$item) {

            switch ($role) {

                case self::ROLE_ONLINE:
                    $this->maskFields($item, [
                        'customer_email',
                        'telephone',
                        'shipping_name',
                        'billing_name',
                        'shipping_address',
                        'billing_address',
                        'postcode'
                    ]);
                    break;

                case self::ROLE_OPERATIONS:
                case self::ROLE_MH_GST:
                case self::ROLE_TG_GST:
                    $this->maskFields($item, [
                        'customer_email',
                        'telephone',
                        'shipping_name',
                        'billing_name',
                        'shipping_address',
                        'billing_address'
                    ]);
                    break;

                case self::ROLE_OUTWARD:
                    $this->maskFields($item, [
                        'customer_email',
                        'shipping_name',
                        'billing_name',
                        'school_name',
                        'student_name'
                    ]);

                    if (isset($item['shipping_address'])) {
                        $item['shipping_address'] =
                            $this->mask->maskAddress($item['shipping_address']);
                    }

                    if (isset($item['billing_address'])) {
                        $item['billing_address'] =
                            $this->mask->maskAddress($item['billing_address']);
                    }
                    break;

                case self::ROLE_SUPPORT:
                case self::ROLE_CS_SUPPORT:
                    $this->maskFields($item, [
                        'shipping_name',
                        'billing_name',
                        'shipping_address',
                        'billing_address',
                        'postcode'
                    ]);
                    break;

                /**
                 * DEFAULT:
                 * Mask everything for unknown roles
                 */
                default:
                    $this->maskFields($item, [
                        'customer_email',
                        'telephone',
                        'shipping_name',
                        'billing_name',
                        'shipping_address',
                        'billing_address',
                        'postcode'
                    ]);
                    break;
            }
        }

        return $result;
    }

    private function maskFields(&$item, $fields)
    {
        foreach ($fields as $field) {

            if (!isset($item[$field])) {
                continue;
            }

            switch ($field) {

                case 'customer_email':
                    $item[$field] = $this->mask->maskEmail($item[$field]);
                    break;

                case 'telephone':
                    $item[$field] = $this->mask->maskPhone($item[$field]);
                    break;

                case 'shipping_name':
                case 'billing_name':
                case 'customer_name':
                case 'school_name':
                case 'student_name':
                    $item[$field] = $this->mask->maskName($item[$field]);
                    break;

                case 'shipping_address':
                case 'billing_address':
                    $item[$field] = $this->mask->maskAddress($item[$field]);
                    break;

                case 'postcode':
                    $item[$field] = '*****';
                    break;
            }
        }
    }
}