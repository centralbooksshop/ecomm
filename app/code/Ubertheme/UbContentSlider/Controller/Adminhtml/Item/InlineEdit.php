<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

use Magento\Backend\App\Action\Context;
use Ubertheme\UbContentSlider\Api\ItemRepositoryInterface as ItemRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Ubertheme\UbContentSlider\Api\Data\ItemInterface;

/**
 * UBCSL slide item grid inline edit controller
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var PostDataProcessor */
    protected $dataProcessor;

    /** @var Slide Item Repository  */
    protected $itemRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param ItemRepository $itemRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        ItemRepository $itemRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->itemRepository = $itemRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $itemId) {
            /** @var \Ubertheme\UbContentSlider\Model\Item $item */
            $item = $this->itemRepository->getById($itemId);
            try {
                $itemData = $this->filterPost($postItems[$itemId]);
                $this->validatePost($itemData, $item, $error, $messages);
                $extendedItemData = $item->getData();
                $this->setItemData($item, $extendedItemData, $itemData);
                $this->itemRepository->save($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithItemId($item, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithItemId($item, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithItemId(
                    $item,
                    __('Something went wrong while saving the slide item.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Filtering posted data.
     *
     * @param array $postData
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $itemData = $this->dataProcessor->filter($postData);
        return $itemData;
    }

    /**
     * Validate post data
     *
     * @param array $itemData
     * @param \Ubertheme\UbContentSlider\Model\Item $item
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(array $itemData, \Ubertheme\UbContentSlider\Model\Item $item, &$error, array &$messages)
    {
        if (!($this->dataProcessor->validate($itemData) && $this->dataProcessor->validateRequireEntry($itemData))) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithItemId($item, $error->getText());
            }
        }
    }

    /**
     * Add item title to error message
     *
     * @param ItemInterface $item
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithItemId(ItemInterface $item, $errorText)
    {
        return '[Slide Item ID: ' . $item->getId() . '] ' . $errorText;
    }

    /**
     * Set slide item data
     *
     * @param \Ubertheme\UbContentSlider\Model\Item $item
     * @param array $extendedItemData
     * @param array $itemData
     * @return $this
     */
    public function setItemData(\Ubertheme\UbContentSlider\Model\Item $item, array $extendedItemData, array $itemData)
    {
        $item->setData(array_merge($item->getData(), $extendedItemData, $itemData));
        return $this;
    }
}
