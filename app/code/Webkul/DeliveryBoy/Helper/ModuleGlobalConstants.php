<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Helper;

abstract class ModuleGlobalConstants
{
    public const DEFAULT_ADMIN_EMAIL_XML_PATH = "trans_email/ident_general/email";
    public const DEFAULT_ADMIN_NAME = 'Admin';
    public const DELIVEYBOY_NEW_ACCOUNT_EMAIL_TEMPLATE_ID = 'deliveryboy_new_account';
    public const DELIVEYBOY_REGISTRATION_NOTIFICATION_TEMPLATE_ID = 'deliveryboy_registration_notification';
    public const DELIVEYBOY_APPROVED_TEMPLATE_ID = 'deliveryboy_approved';
    public const DELIVEYBOY_REVIEW_EVALUATION_EMAIL_TEMPLATE_ID = 'deliveryboy_review_evaluation_template';
    public const RATING_MAX_VALUE = 5;
    public const DEFAULT_RATING_MANAGER_NAME = 'Admin';
    public const XML_PATH_SORT_DELIVERYBOY_BY_NEAREST_DISTANCE
        = 'deliveryboy/delivery_automation/sort_by_nearest_distance';
    public const XML_PATH_AUTO_ASSIGN_NEAREST_DELIVERYBOY
        = 'deliveryboy/delivery_automation/auto_assign_nearest_deliveryboy';
}
