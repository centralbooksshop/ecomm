<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Exception\LocalizedException;

class ForgotPassword extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Block\Order\Info $orderInfoBlock
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Webkul\DeliveryBoy\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Sales\Model\Convert\Order $orderConverter
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\DeliveryBoy\Model\TokenFactory $tokenFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteManager
     * @param \Webkul\DeliveryBoy\Model\RatingFactory $ratingFactory
     * @param \Magento\Weee\Block\Item\Price\Renderer $priceRenderer
     * @param \Webkul\DeliveryBoy\Block\Sales\Order\Totals $orderTotals
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrder
     * @param \Magento\Sales\Model\Order\Status $orderStatusCollection
     * @param \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Webkul\DeliveryBoy\Model\CommentFactory $deliveryboyComment
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer
     * @param \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryboyRepository
     * @param \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory $deliveryboyDataFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory $commentCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollection
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Webkul\DeliveryBoy\Helper\Authentication $authHelper
     * @param \Webkul\DeliveryBoy\Helper\DeliveryAutomation $deliveryAutomationHelper
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     */
    public function __construct(
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Block\Order\Info $orderInfoBlock,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Webkul\DeliveryBoy\Helper\Catalog $helperCatalog,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\Convert\Order $orderConverter,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\DeliveryBoy\Model\TokenFactory $tokenFactory,
        \Magento\Store\Model\WebsiteFactory $websiteManager,
        \Webkul\DeliveryBoy\Model\RatingFactory $ratingFactory,
        \Magento\Weee\Block\Item\Price\Renderer $priceRenderer,
        \Webkul\DeliveryBoy\Block\Sales\Order\Totals $orderTotals,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrder,
        \Magento\Sales\Model\Order\Status $orderStatusCollection,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Webkul\DeliveryBoy\Model\CommentFactory $deliveryboyComment,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer,
        \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryboyRepository,
        \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory $deliveryboyDataFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $ratingCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory $commentCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollection,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Webkul\DeliveryBoy\Helper\Authentication $authHelper,
        \Webkul\DeliveryBoy\Helper\DeliveryAutomation $deliveryAutomationHelper,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement
    ) {
        $this->accountManagement = $accountManagement;

        parent::__construct(
            $emulate,
            $config,
            $filesystem,
            $mathRandom,
            $transaction,
            $context,
            $jsonHelper,
            $orderFactory,
            $orderInfoBlock,
            $dir,
            $helperCatalog,
            $date,
            $orderConverter,
            $deliveryboyHelper,
            $deliveryboy,
            $resource,
            $tokenFactory,
            $websiteManager,
            $ratingFactory,
            $priceRenderer,
            $orderTotals,
            $dateTimeFactory,
            $customerFactory,
            $storeManager,
            $deliveryboyOrder,
            $orderStatusCollection,
            $shipmentNotifier,
            $encryptor,
            $invoiceService,
            $orderRepository,
            $deliveryboyComment,
            $timezone,
            $priceFormatter,
            $transportBuilder,
            $invoiceSender,
            $fileUploaderFactory,
            $inlineTranslation,
            $orderCollection,
            $orderItemRenderer,
            $deliveryboyRepository,
            $deliveryboyDataFactory,
            $tokenResourceCollection,
            $ratingCollection,
            $commentCollection,
            $deliveryboyOrderResourceCollection,
            $deliveryboyResourceCollection,
            $logger,
            $operationHelper,
            $fileDriver,
            $authHelper,
            $deliveryAutomationHelper
        );
    }

    /**
     * Execute forgot password action for admin.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $emailValidator = new \Zend\Validator\EmailAddress();
            if (!$emailValidator->isValid($this->email)) {
                $this->returnArray["message"] = (string)__("Invalid email address.");
                return $this->getJsonResponse($this->returnArray);
            }
            $customer = $this->customerFactory->create()->setWebsiteId($this->websiteId)->loadByEmail($this->email);
            if ($customer->getId() > 0) {
                try {
                    $this->accountManagement->initiatePasswordReset(
                        $this->email,
                        \Magento\Customer\Model\AccountManagement::EMAIL_REMINDER,
                        $customer->getWebsiteId()
                    );
                    $this->returnArray["success"] = true;
                    $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
                    $this->returnArray["message"] = (string)__(
                        "If there is an account associated with %1 you will receive"
                        ." an email with a link to reset your password.",
                        $this->email
                    );
                } catch (\Throwable $e) {
                    $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
                    $this->returnArray["message"] = (string)__($e->getMessage());
                    return $this->getJsonResponse($this->returnArray);
                }
            } else {
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = (string)__(
                    "If there is an account associated with %1 you".
                    " will receive an email with a link to reset your password.",
                    $this->email
                );
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = (string)__($e->getMessage());
            $this->returnArray["trace"] = $e->getTrace();
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->email = $this->wholeData["email"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->websiteId = $this->wholeData["websiteId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
