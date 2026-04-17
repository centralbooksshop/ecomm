<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Plumrocket\RMA\Model\Config\Source\Status;
use Plumrocket\RMA\Model\ResponseFactory;

/**
 * @since 2.4.0
 */
class CreateQuickResponseTemplates implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        foreach ($this->getQuickResponseTemplates() as $template) {
            $this->responseFactory->create()
                ->setData($template)
                ->save();
        }
    }

    /**
     * Get default quick response templates settings
     *
     * @return array
     */
    private function getQuickResponseTemplates(): array
    {
        return [
            [
                'title' => 'Thank you for request',
                'status' => Status::STATUS_ENABLED,
                'store_id' => 0,
                'message' => '<p>Dear Customer,</p>
<p>Your RMA request has been received and is being reviewed by our customer service team. <br />We will get back to you shortly.</p>
<p>Best Regards, <br />Store Customer Service Team</p>',
            ],
            [
                'title' => 'Return Delivery Instruction',
                'status' => Status::STATUS_ENABLED,
                'store_id' => 0,
                'message' => '<p>Dear Customer,</p>
<p>Your return (<strong>RMA Number: XXX</strong>) was successfully delivered to the returns warehouse on <strong>XX/XX/201X</strong>. Once the returned package is opened, we will notify you via email to inform you of the contents received. Please allow 3-5 business days. Due to item availability, some repair/replacement returns may be delayed or refunded. If you have any questions or need further assistance, please visit our Customer Service Contact Us Page.</p>
<p>Best Regards,</p>
<p>Store Customer Service Team</p>',
            ],
            [
                'title' => 'RMA has been processed',
                'status' => Status::STATUS_ENABLED,
                'store_id' => 0,
                'message' => '<p>Dear Customer,</p>
<p>Your return (<strong>RMA Number: ХХХ</strong>) has been processed, which means that the items have been verified and your return has been approved.</p>
<p>If your return was processed for a refund, you should receive your refund to your original form of payment within 3 - 5 business days.</p>
<p>If your return was processed for a replacement, a replacement order will be processed and set up to be shipped within the next 1-2 business days.</p>
<p>Please let us know, if you need any assitance.</p>
<p>Best Regards, <br />Store Customer Service Team</p>',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion(): string
    {
        return '2.0.0';
    }
}
