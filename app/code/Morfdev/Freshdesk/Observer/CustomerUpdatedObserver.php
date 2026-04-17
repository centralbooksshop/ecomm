<?php
namespace Morfdev\Freshdesk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressRenderer;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Morfdev\Freshdesk\Model\Webhook;
use Psr\Log\LoggerInterface;

class CustomerUpdatedObserver implements ObserverInterface
{
	/** @var Webhook  */
	protected $webhook;

	/** @var CustomerRepositoryInterface  */
	protected $customerRepository;

	/** @var AddressRepositoryInterface  */
	protected $addressRepository;

	/** @var AddressRenderer */
	protected $addressRenderer;

	/** @var  AddressMapper */
	protected $addressMapper;

	/** @var LoggerInterface  */
	protected $logger;

	/**
	 * CustomerUpdatedObserver constructor.
	 * @param CustomerRepositoryInterface $customerRepository
	 * @param AddressRepositoryInterface $addressRepository
	 * @param AddressRenderer $addressRenderer
	 * @param AddressMapper $mapper
	 * @param LoggerInterface $logger
	 * @param Webhook $webhook
	 */
	public function __construct(
		CustomerRepositoryInterface $customerRepository,
		AddressRepositoryInterface $addressRepository,
		AddressRenderer $addressRenderer,
		AddressMapper $mapper,
		LoggerInterface $logger,
		Webhook $webhook
	) {
		$this->customerRepository = $customerRepository;
		$this->addressRepository = $addressRepository;
		$this->addressRenderer = $addressRenderer;
		$this->addressMapper = $mapper;
		$this->webhook = $webhook;
		$this->logger = $logger;
	}

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$customerAddress = $observer->getCustomerAddress();
		try {
			$customer = $this->customerRepository->getById($customerAddress->getCustomerId());
		} catch (\Exception $e) {
			$customer = null;
		}
		if (!$customer) {
			return;
		}

		$addressRenderer = $this->addressRenderer->getFormatByCode('html')->getRenderer();
		try {
			if ($customer->getDefaultBilling()) {
				$billingAddress = $this->addressRepository->getById($customer->getDefaultBilling());
			} else {
				$billingAddress = $customerAddress->getDataModel();
			}
			$billingAddressFormatted = $addressRenderer->renderArray($this->addressMapper->toFlatArray($billingAddress));
			$phone = $billingAddress->getTelephone();
			$company = $billingAddress->getCompany();
			$address = [
				'address_1' => $customerAddress->getStreetLine(1),
				'address_2' => $customerAddress->getStreetLine(2),
				'city' => $customerAddress->getCity(),
				'state' => $customerAddress->getRegion(),
				'country' => $customerAddress->getCountryModel()->getName(),
				'postcode' => $customerAddress->getPostcode()

			];
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
			$billingAddressFormatted = '';
			$phone = '';
			$company = '';
			$address = [];
		}

		$data = [
			'scope' => "customer.updated",
			'email' => $customer->getEmail(),
			'first_name' => $customer->getFirstname(),
			'last_name' => $customer->getLastname(),
			'phone' => $phone,
			'addressFormatted' => $billingAddressFormatted,
			'address' => $address,
			'company' => $company
		];
		$this->webhook->sendData($data);
	}
}
