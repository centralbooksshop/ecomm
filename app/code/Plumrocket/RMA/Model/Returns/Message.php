<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns;

use Magento\Framework\Model\AbstractModel;
use Plumrocket\RMA\Api\Data\ReturnMessageInterface;

class Message extends AbstractModel implements ReturnMessageInterface
{
    /**
     * @var \Plumrocket\RMA\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Plumrocket\RMA\Helper\Returns
     */
    protected $returnsHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * Message constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Plumrocket\RMA\Helper\Config $configHelper
     * @param \Plumrocket\RMA\Helper\Returns $returnsHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Plumrocket\RMA\Helper\Config $configHelper,
        \Plumrocket\RMA\Helper\Returns $returnsHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->returnsHelper = $returnsHelper;
        $this->escaper = $escaper;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->filterProvider = $filterProvider;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\ResourceModel\Returns\Message');
    }

    /**
     * @inheritDoc
     */
    public function getReturnId(): int
    {
        return (int) $this->getData(self::RETURN_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReturnId(int $returnId): ReturnMessageInterface
    {
        $this->setData(self::RETURN_ID, $returnId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFromId(): int
    {
        return (int) $this->getData(self::FROM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setFromId(int $fromId): ReturnMessageInterface
    {
        $this->setData(self::FROM_ID, $fromId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return (string) $this->getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): ReturnMessageInterface
    {
        $this->setData(self::TYPE, $type);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): ReturnMessageInterface
    {
        $this->setData(self::NAME, $name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContentHtml(): string
    {
        return nl2br((string) $this->filterProvider->getBlockFilter()->filter($this->getText()));
    }

    /**
     * @inheritDoc
     */
    public function getText(): string
    {
        $text = $this->getData('text');
        if(!empty($text)) {
			if (self::FROM_MANAGER !== $this->getType()) {
				$text = $this->escaper->escapeHtml($text, ['b', 'br', 'strong', 'i', 'u']);
			}
		} else {
          $text = '';
		}

        return $text;
    }

    /**
     * @inheritDoc
     */
    public function setText(string $text): ReturnMessageInterface
    {
        $this->setData(self::TEXT, $text);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFiles()
    {
        return $this->getData(self::FILES);
    }

    /**
     * @inheritDoc
     */
    public function getPreparedFiles()
    {
        $result = [];
        $files = $this->getFiles();
        if (! is_array($files)) {
            $files = (array)json_decode((string) $files, true);
        }

        foreach ($files as $filename) {
            $result[] = [
                'filename' => $filename,
                'name' => basename($filename)
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setPreparedFiles($preparedFiles): ReturnMessageInterface
    {
        $this->setData(self::FILES, json_encode($preparedFiles));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsSystem(): int
    {
        return $this->getData(self::IS_SYSTEM);
    }

    /**
     * @inheritDoc
     */
    public function setIsSystem(int $isSystem): ReturnMessageInterface
    {
        $this->setData(self::IS_SYSTEM, $isSystem);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsInternal(): int
    {
        return $this->getData(self::IS_INTERNAL);
    }

    /**
     * @inheritDoc
     */
    public function setIsInternal(int $isInternal): ReturnMessageInterface
    {
        $this->setData(self::IS_INTERNAL, $isInternal);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): ReturnMessageInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): ReturnMessageInterface
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }
}
