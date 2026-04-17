<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\User\Model\UserFactory;
use Plumrocket\RMA\Api\Data\ReturnInterface;
use Plumrocket\RMA\Api\Data\ReturnItemInterfaceFactory;
use Plumrocket\RMA\Api\Data\ReturnMessageInterface;
use Plumrocket\RMA\Api\Data\ReturnMessageSearchResultInterfaceFactory;
use Plumrocket\RMA\Api\Data\TrackingNumberInterface;
use Plumrocket\RMA\Api\Data\TrackingNumberInterfaceFactory;
use Plumrocket\RMA\Api\Data\TrackingNumberSearchResultInterfaceFactory;
use Plumrocket\RMA\Api\ReturnManagementInterface;
use Plumrocket\RMA\Api\ReturnManagerRepositoryInterface;
use Plumrocket\RMA\Api\ReturnRepositoryInterface;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\AddressFactory;
use Plumrocket\RMA\Model\Returns\EmailFactory;
use Plumrocket\RMA\Model\Returns\ValidatorFactory;

/**
 * @since 2.3.0
 */
class ReturnManagement implements ReturnManagementInterface
{
    /**
     * @var \Plumrocket\RMA\Api\Data\TrackingNumberInterfaceFactory
     */
    protected $trackFactory;

    /**
     * @var \Plumrocket\RMA\Api\Data\TrackingNumberSearchResultInterfaceFactory
     */
    protected $trackingNumberSearchResultsFactory;

    /**
     * @var \Plumrocket\RMA\Api\Data\ReturnMessageSearchResultInterfaceFactory
     */
    private $returnMessageSearchResultsFactory;

    /**
     * @var \Plumrocket\RMA\Api\ReturnRepositoryInterface
     */
    private $returnRepository;

    /**
     * @var \Plumrocket\RMA\Api\ReturnManagerRepositoryInterface
     */
    private $managerRepository;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Plumrocket\RMA\Model\Returns\EmailFactory
     */
    private $emailFactory;

    /**
     * @var \Plumrocket\RMA\Model\Returns\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Plumrocket\RMA\Helper\Returns
     */
    private $returnsHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Plumrocket\RMA\Api\Data\ReturnItemInterfaceFactory
     */
    private $returnItemFactory;

    /**
     * @var \Plumrocket\RMA\Helper\Returns\Item
     */
    private $itemHelper;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var \Plumrocket\RMA\Model\Config\Source\ReturnsStatus
     */
    private $returnsStatusSource;

    /**
     * @var \Plumrocket\RMA\Model\AddressFactory
     */
    private $addressFactory;

    /**
     * @param \Plumrocket\RMA\Api\Data\TrackingNumberInterfaceFactory             $trackFactory
     * @param \Plumrocket\RMA\Api\Data\TrackingNumberSearchResultInterfaceFactory $trackingNumberSearchResultsFactory
     * @param \Plumrocket\RMA\Api\Data\ReturnMessageSearchResultInterfaceFactory  $returnMessageSearchResultsFactory
     * @param \Plumrocket\RMA\Api\ReturnRepositoryInterface                       $returnRepository
     * @param \Plumrocket\RMA\Api\ReturnManagerRepositoryInterface                $managerRepository
     * @param \Magento\User\Model\UserFactory                                     $userFactory
     * @param \Plumrocket\RMA\Model\Returns\EmailFactory                          $emailFactory
     * @param \Plumrocket\RMA\Model\Returns\ValidatorFactory                      $validatorFactory
     * @param \Magento\Framework\Api\FilterBuilder                                $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                        $criteriaBuilder
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface  $collectionProcessor
     * @param \Plumrocket\RMA\Helper\Returns                                      $returnsHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                         $dateTime
     * @param \Plumrocket\RMA\Api\Data\ReturnItemInterfaceFactory                 $returnItemFactory
     * @param \Plumrocket\RMA\Helper\Returns\Item                                 $itemHelper
     * @param \Magento\Sales\Model\Order\ItemFactory                              $orderItemFactory
     * @param \Plumrocket\RMA\Model\Config\Source\ReturnsStatus                   $returnsStatusSource
     * @param \Plumrocket\RMA\Model\Returns\AddressFactory                        $addressFactory
     */
    public function __construct(
        TrackingNumberInterfaceFactory $trackFactory,
        TrackingNumberSearchResultInterfaceFactory $trackingNumberSearchResultsFactory,
        ReturnMessageSearchResultInterfaceFactory $returnMessageSearchResultsFactory,
        ReturnRepositoryInterface $returnRepository,
        ReturnManagerRepositoryInterface $managerRepository,
        UserFactory $userFactory,
        EmailFactory $emailFactory,
        ValidatorFactory $validatorFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $criteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        ReturnsHelper $returnsHelper,
        DateTime $dateTime,
        ReturnItemInterfaceFactory $returnItemFactory,
        ItemHelper $itemHelper,
        OrderItemFactory $orderItemFactory,
        ReturnsStatus $returnsStatusSource,
        AddressFactory $addressFactory
    ) {
        $this->trackFactory = $trackFactory;
        $this->returnMessageSearchResultsFactory = $returnMessageSearchResultsFactory;
        $this->trackingNumberSearchResultsFactory = $trackingNumberSearchResultsFactory;
        $this->returnRepository = $returnRepository;
        $this->managerRepository = $managerRepository;
        $this->userFactory = $userFactory;
        $this->emailFactory = $emailFactory;
        $this->validatorFactory = $validatorFactory;
        $this->filterBuilder = $filterBuilder;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->returnsHelper = $returnsHelper;
        $this->dateTime = $dateTime;
        $this->returnItemFactory = $returnItemFactory;
        $this->itemHelper = $itemHelper;
        $this->orderItemFactory = $orderItemFactory;
        $this->returnsStatusSource = $returnsStatusSource;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @inheritDoc
     */
    public function getTrackingNumbers(int $id)
    {
        $collection = $this->trackFactory->create()
            ->getCollection()
            ->addReturnsFilter($id)
            ->setOrder('entity_id', 'asc');

        /** @var \Plumrocket\RMA\Api\Data\TrackingNumberSearchResultInterface $searchResults */
        $searchResults = $this->trackingNumberSearchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function addTrackingNumber(int $id, TrackingNumberInterface $track): bool
    {
        $validator = $this->validatorFactory->create();

        $validator->validateTrack(
            $track->getCarrierCode() ?: null,
            $track->getTrackNumber() ?: null
        );

        if (! $validator->isValid()) {
            $message = array_shift($validator->getMessages());
            throw new LocalizedException(__($message));
        }

        return (bool) $track->setReturnId($id)
            ->save();
    }

    /**
     * @inheritDoc
     */
    public function removeTrackingNumber(int $id, int $trackId): bool
    {
        $this->criteriaBuilder->addFilters(
            ['eq' => $this->filterBuilder->setField(TrackingNumberInterface::IDENTIFIER)->setValue($trackId)->create()]
        );
        $this->criteriaBuilder->addFilters(
            ['eq' => $this->filterBuilder->setField(TrackingNumberInterface::RETURN_ID)->setValue($id)->create()]
        );

        $collection = $this->trackFactory->create()->getCollection();
        $this->collectionProcessor->process($this->criteriaBuilder->create(), $collection);

        $counter = 0;
        foreach ($collection as $track) {
            $track->delete();
            $counter++;
        }

        return $counter === count($collection);
    }

    /**
     * @inheritDoc
     */
    public function getMessagesList(int $id)
    {
        $return = $this->returnRepository->getById($id);

        /** @var \Plumrocket\RMA\Api\Data\ReturnMessageSearchResultInterface $searchResults */
        $searchResults = $this->returnMessageSearchResultsFactory->create();
        $searchResults->setItems($return->getMessages());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function addMessage(int $id, ReturnMessageInterface $message): bool
    {
        if (! $this->managerRepository->managerExists($message->getFromId())) {
            throw new NoSuchEntityException(
                __('The manager with the "%1" ID wasn\'t found. Verify the ID and try again.', $message->getFromId())
            );
        }

        $return = $this->returnRepository->getById($id);
        $manager = $this->userFactory->create()->load($message->getFromId());

        // Add message.
        $result = $return->addMessage(
            ReturnMessageInterface::FROM_MANAGER,
            $message->getText(),
            null,
            $message->getIsSystem(),
            $message->getIsInternal(),
            $manager
        );

        if ($result instanceof ReturnMessageInterface) {
            // Send email.
            /** @var \Plumrocket\RMA\Model\Returns\Email $email */
            $email = $this->emailFactory->create();
            $email->setReturns($return)
                ->setMessage($result)
                ->notifyManagerAboutUpdate($manager);

            if (! $message->getIsInternal()) {
                $email->notifyCustomerAboutUpdate();
            }
        } else {
            throw new LocalizedException(__('The message in not valid'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function authorize(int $id): ReturnInterface
    {
        $return = $this->returnRepository->getById($id);
        $items = [];

        foreach ($return->getItems() as $item) {
            $item->setQtyAuthorized($item->getQtyRequested());
            $items[] = $item;
        }

        $return->setItems($items);

        return $this->save($return);
    }

    /**
     * @inheritDoc
     */
    public function receive(int $id): ReturnInterface
    {
        $return = $this->returnRepository->getById($id);
        $items = [];

        foreach ($return->getItems() as $item) {
            if (! $item->getQtyAuthorized()) {
                throw new NoSuchEntityException(
                    __('Authorized Qty not specified for one of the products')
                );
            }

            $item->setQtyReceived($item->getQtyAuthorized());
            $items[] = $item;
        }

        $return->setItems($items);

        return $this->save($return);
    }

    /**
     * @inheritDoc
     */
    public function approve(int $id): ReturnInterface
    {
        $return = $this->returnRepository->getById($id);
        $items = [];

        foreach ($return->getItems() as $item) {
            if (! $item->getQtyReceived()) {
                throw new NoSuchEntityException(
                    __('Received Qty not specified for one of the products')
                );
            }

            $item->setQtyApproved($item->getQtyReceived());
            $items[] = $item;
        }

        $return->setItems($items);

        return $this->save($return);
    }

    /**
     * @inheritDoc
     */
    public function cancel(int $id): ReturnInterface
    {
        $return = $this->returnRepository->getById($id);

        if ($return->isClosed()) {
            return $return;
        }

        $return = $return->setIsClosed(true)
            ->setStatus(ReturnsStatus::STATUS_CANCELLED);

        $return = $this->save($return);

        $manager = $this->userFactory->create()->load($return->getManagerId());

        // Add system message.
        $systemMessage = $return->addMessage(
            ReturnMessageInterface::FROM_MANAGER,
            __('Return request has been canceled by store manager'),
            null,
            true,
            false,
            $manager
        );

        // Send email.
        $email = $this->emailFactory->create()
            ->setReturns($return)
            ->setMessage($systemMessage)
            ->notifyCustomerAboutUpdate();

        if ($return->getManagerId()) {
            $email->notifyManagerAboutUpdate($manager);
        }

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function save(ReturnInterface $return): ReturnInterface
    {
        $date = $this->dateTime->gmtDate();

        $return->setUpdatedAt($date);

        if (!$return->getId()) {
            $return->setCreatedAt($date)
                   ->setReadMarkAt($date);
        }

        $this->_beforeSave($return);
        $this->returnRepository->save($return);
        $this->_afterSave($return);

        return $this->returnRepository->getById((int) $return->getId(), true);
    }

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnInterface|\Plumrocket\RMA\Model\Returns $model
     * @return void
     * @throws ValidatorException
     */
    protected function _beforeSave(ReturnInterface $model)
    {
        if ($model->isObjectNew() &&
            ! $this->returnsHelper->canReturnAdmin($model->getOrder())
        ) {
            return;
        }

        // Validate data.
        /** @var \Plumrocket\RMA\Model\Returns\Validator $validator */
        $validator = $this->validatorFactory->create()
                                            ->setReturns($model);

        if (! $model->isClosed()) {
            $items = [];
            foreach ($model->getItems() as $item) {
                if ($item->getData(ItemHelper::ENTITY_ID) === null) {
                    $item->setData(ItemHelper::ENTITY_ID, '');
                }
                $items[] = $item;
            }

            $validator->validateItemsAdmin($items);
        }

        if (! $validator->isValid()) {
            foreach ($validator->getMessages() as $message) {
                throw new ValidatorException(__($message));
            }

            return;
        }

        $model->setValidItems($validator->getValidItems());
    }

    /**
     * @param \Plumrocket\RMA\Api\Data\ReturnInterface|\Plumrocket\RMA\Model\Returns $model
     */
    protected function _afterSave(ReturnInterface $model)
    {
        $validItems = $model->getValidItems();

        if (is_array($validItems)) {
            $hasItemChanges = false;
            foreach ($validItems as $data) {
                /** @var \Plumrocket\RMA\Api\Data\ReturnItemInterface|\Plumrocket\RMA\Model\Returns\Item $item */
                $item = $this->returnItemFactory->create();

                if (! array_key_exists(ItemHelper::ENTITY_ID, $data) || '' === $data[ItemHelper::ENTITY_ID]) {
                    $orderItem = $this->orderItemFactory->create()
                                                        ->load($data[ItemHelper::ORDER_ITEM_ID]);

                    $item->setReturns($model)
                         ->setQtyPurchased(
                             $this->itemHelper->getQtyToReturn($orderItem, $model->getId())
                         );

                    $data[ItemHelper::QTY_AUTHORIZED] = $data[ItemHelper::QTY_REQUESTED];
                } else {
                    $item->load($data[ItemHelper::ENTITY_ID]);
                    if (! $item->getId()
                        || $item->getOrderItemId() != $data[ItemHelper::ORDER_ITEM_ID]
                        || $item->getReturnId() != $model->getId()
                    ) {
                        continue;
                    }
                }

                // Prepare data before save.
                unset($data[ItemHelper::ENTITY_ID]);

                $cols = [
                    ItemHelper::QTY_AUTHORIZED,
                    ItemHelper::QTY_RECEIVED,
                    ItemHelper::QTY_APPROVED,
                ];

                foreach ($cols as $col) {
                    if (isset($data[$col]) && '' === $data[$col]) {
                        $data[$col] = null;
                    }
                }

                $item->addData($data)->save();
                $hasItemChanges = true;
            }

            if ($hasItemChanges) {
                // If items was created then reset items in model.
                $model->setItems(null);
            }
        }

        // Calculate and save new status.
        $statusChanged = false;
        $status = $this->returnsHelper->getStatus($model);
        if ($status && $status != $model->getStatus() && ! $model->isClosed()) {
            // If it is one of final statuses then close return.
            if (array_key_exists($status, $this->returnsStatusSource->getFinalStatuses())) {
                $model->setIsClosed(true);
            }

            $model->setStatus($status)->save();
            $statusChanged = true;
        }

        $email = $this->emailFactory->create()
                                    ->setReturns($model);

        // New object after save.
        if ($model->isObjectNew()) {
            // Assign address.
            $address = $model->getAddress();

            if (! $address || ! $address->getId()) {
                $unassignedAddress = $this->addressFactory->create()
                                                          ->getUnassigned($model->getOrder()->getId());

                if ($unassignedAddress) {
                    $unassignedAddress->setParentId($model->getId())
                                      ->save();
                }
            }
        } else {
            $manager = $this->userFactory->create()->load($model->getManagerId());

            // Add system message if status is changed.
            $systemMessage = null;
            if ($statusChanged) {

                $systemMessage = $model->addMessage(
                    ReturnMessageInterface::FROM_MANAGER,
                    __('Status of return request has been updated to: %1', $model->getStatusLabel()),
                    null,
                    true,
                    false,
                    $manager
                );
            }

            // If return is updated, send emails only if message exists
            if ($systemMessage) {
                $email->setMessage($systemMessage);
                $email->notifyManagerAboutUpdate($manager);
                $email->notifyCustomerAboutUpdate();
            }
        }
    }
}
