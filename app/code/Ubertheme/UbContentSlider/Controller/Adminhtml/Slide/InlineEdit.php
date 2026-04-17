<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action\Context;
use Ubertheme\UbContentSlider\Api\SlideRepositoryInterface as SlideRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Ubertheme\UbContentSlider\Api\Data\SlideInterface;

/**
 * Ubcsl slide grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var PostDataProcessor */
    protected $dataProcessor;

    /** @var SlideRepository  */
    protected $slideRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param SlideRepository $slideRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        SlideRepository $slideRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->slideRepository = $slideRepository;
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

        foreach (array_keys($postItems) as $slideId) {
            /** @var \Ubertheme\UbContentSlider\Model\Slide $slide */
            $slide = $this->slideRepository->getById($slideId);
            try {
                $slideData = $this->filterPost($postItems[$slideId]);
                $this->validatePost($slideData, $slide, $error, $messages);
                $extendedSlideData = $slide->getData();
                $this->setSlideData($slide, $extendedSlideData, $slideData);
                $this->slideRepository->save($slide);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithSlideId($slide, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithSlideId($slide, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithSlideId(
                    $slide,
                    __('Something went wrong while saving the slider.')
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
        $slideData = $this->dataProcessor->filter($postData);
        return $slideData;
    }

    /**
     * Validate post data
     *
     * @param array $slideData
     * @param \Ubertheme\UbContentSlider\Model\Slide $slide
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(array $slideData, \Ubertheme\UbContentSlider\Model\Slide $slide, &$error, array &$messages)
    {
        if (!($this->dataProcessor->validate($slideData) && $this->dataProcessor->validateRequireEntry($slideData))) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithSlideId($slide, $error->getText());
            }
        }
    }

    /**
     * Add slide title to error message
     *
     * @param SlideInterface $slide
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithSlideId(SlideInterface $slide, $errorText)
    {
        return '[Slide ID: ' . $slide->getId() . '] ' . $errorText;
    }

    /**
     * Set slide data
     *
     * @param \Ubertheme\UbContentSlider\Model\Slide $slide
     * @param array $extendedSlideData
     * @param array $slideData
     * @return $this
     */
    public function setSlideData(\Ubertheme\UbContentSlider\Model\Slide $slide, array $extendedSlideData, array $slideData)
    {
        $slide->setData(array_merge($slide->getData(), $extendedSlideData, $slideData));
        return $this;
    }
}
