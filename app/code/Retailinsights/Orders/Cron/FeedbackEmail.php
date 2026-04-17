<?php
namespace Retailinsights\Orders\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class FeedbackEmail
{
    protected $orderCollectionFactory;
    protected $transportBuilder;
    protected $scopeConfig;
    protected $storeManager;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

	public function execute()
	{
		// Target orders where updated_at = 8 days ago
		$fromDate = (new \DateTime('-8 days'))->format('Y-m-d 00:00:00');
		$toDate   = (new \DateTime('-8 days'))->format('Y-m-d 23:59:59');

		$orders = $this->orderCollectionFactory->create()
			->addFieldToFilter('updated_at', ['from' => $fromDate, 'to' => $toDate])
			->addFieldToFilter('status', ['in' => ['order_delivered']]);

		foreach ($orders as $order) {
			$customerName = trim($order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
			$feedbackLink = 'https://forms.gle/2P7H9EzTS6axDBSW8';

			$transport = $this->transportBuilder
				->setTemplateIdentifier('feedback_request_email_template') // email_templates.xml
				->setTemplateOptions([
					'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
					'store' => $this->storeManager->getStore()->getId()
				])
				->setTemplateVars([
					'customer_name'   => $customerName ?: 'Valued Customer',
					'order_increment' => $order->getIncrementId(),
					'order_date'      => date('d-M-Y', strtotime($order->getCreatedAt())),
					'feedback_link'   => $feedbackLink
				])
				->setFrom('general')
				->addTo($order->getCustomerEmail(), $customerName)
				->getTransport();

			$transport->sendMessage();
		}

		return $this;
	}

}
