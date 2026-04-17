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
namespace Webkul\DeliveryBoy\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Webkul\DeliveryBoy\Model\ResourceModel\VehicleType\CollectionFactory as VehicleTypeCollF;
use Webkul\DeliveryBoy\Api\Data\VehicleTypeInterface;
use Webkul\DeliveryBoy\Api\Data\VehicleTypeSearchResultInterfaceFactory as SearchResultFactory;

class VehicleTypeRepository implements \Webkul\DeliveryBoy\Api\VehicleTypeRepositoryInterface
{
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var OrderInterface[]
     */
    protected $registry = [];

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var VehicleTypeCollF
     */
    private $VehicleTypeCollF;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param JsonSerializer|null $serializer
     * @param JoinProcessorInterface|null $extensionAttributesJoinProcessor
     * @param VehicleTypeCollF $vehicleTypeCollF
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        JsonSerializer $serializer = null,
        JoinProcessorInterface $extensionAttributesJoinProcessor = null,
        VehicleTypeCollF $vehicleTypeCollF
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->serializer = $serializer;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->vehicleTypeCollF = $vehicleTypeCollF;
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('An ID is needed. Set the ID and try again.'));
        }
        if (!isset($this->registry[$id])) {
            /** @var VehicleTypeInterface $entity */
            $entity = $this->vehicleTypeCollF->create()
            ->addFieldToFilter(
                VehicleTypeInterface::ENTITY_ID,
                $id
            )->getFirstItem();
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(
                    __("The entity that was requested doesn't exist. Verify the entity and try again.")
                );
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }

    /**
     * @inheritDoc
     */
    public function delete(VehicleTypeInterface $entity)
    {
        $entity->delete();
        unset($this->registry[$entity->getEntityId()]);
        return true;
    }

    /**
     * Delete entity by Id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        $entity = $this->get($id);
        return $this->delete($entity);
    }

    /**
     * @inheritDoc
     */
    public function save(VehicleTypeInterface $entity)
    {
        $entity->save();
        $this->registry[$entity->getEntityId()] = $entity;
        return $this->registry[$entity->getEntityId()];
    }
}
