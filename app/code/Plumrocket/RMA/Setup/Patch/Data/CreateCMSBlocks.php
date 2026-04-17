<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Setup\Patch\Data;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * @since 2.4.0
 */
class CreateCMSBlocks implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->createReturnPolicyBlock();
        $this->createSuccessPageBlock();
        $this->createReturnInstructionsBlock();
    }

    /**
     * Create return policy CMS block
     *
     * @return void
     */
    private function createReturnPolicyBlock()
    {
        $block = $this->blockFactory->create();
        $blockIdentifier = 'prrma_policy';
        $block->setStoreId(0)->load($blockIdentifier);

        if (! $block->getId()) {
            $content = <<<TEXT
<p>Items purchased at Yourdomain.com may be returned either to a store or by mail, unless stated otherwise in the list of exceptions below</p>
<p style="color: #ff0000;">Items must be returned in the original manufacturer's packaging. We highly recommend you keep your packaging for at least the first 90 days after purchase.</p>
<p>Items purchased from a Marketplace retailer cannot be returned to Yourdomain.com; they must be returned to their Marketplace Retailer in accordance with their returns policy. Please email the Marketplace retailer directly.</p>
<p><span style="font-size: 1.5em;"><br /></span></p>
<p><span style="font-size: 1.5em;">Returns Policy by Department</span></p>
<p></p>
<h3>Clothing, Shoes and Accessories</h3>
<h4>Must be returned within 90 days</h4>
<ul>
<li>Items purchased at Yourcompany.com can be refunded with a receipt or exchanged within 90 days of purchase.</li>
</ul>
<p></p>
<h3>Sports and Fitness</h3>
<h4>Must be returned within 90 days unless listed below</h4>
<p>Oversized table games and treadmills may be returned to a Yourcompany store or by freight shipping. Under some circumstances, you may be charged for return shipping.</p>
<p>To return an item by freight, please contact Customer Care for assistance. They will also be able to inform you of any return shipping costs.</p>
<p>Autographed sports memorabilia must be returned with the included Certificate of Authenticity.</p>
<p>Swimming pools must be returned within 90 days of receipt.</p>
<p></p>
<h3>Electronics</h3>
<h4>Must be returned within 90 days - with these exceptions:</h4>
<p>The <b>following electronics</b> items must be returned within <b>15 days of receipt</b>:</p>
<ul>
<li>Computers</li>
<li>
<ul>
<li>Computer hardware</li>
<li>Printers (including 3D printers)</li>
<li>3D printing supplies and products</li>
</ul>
</li>
<li>Camcorders</li>
<li>Digital cameras</li>
<li>GPS units</li>
<li>Digital music players</li>
<li>Tablets</li>
<li>E-readers</li>
<li>Portable video players</li>
<li>Drones</li>
</ul>
<h4>Must be returned within 90 days:</h4>
<ul>
<li>Televisions</li>
<li>Computer software must be returned unopened</li>
<li>There are no returns or refunds on prepaid cellular phone cards and electronically fulfilled PINs or minutes.</li>
</ul>
<h4>Must be returned within 14 days and 15 days:</h4>
<ul>
<li>Post-paid cell phones must be returned within 14 days of receipt.</li>
<li>Pre-paid cell phones must be returned within 15 days of receipt.</li>
<li>There are no returns or refunds on prepaid cellular phone cards and electronically fulfilled PINs or minutes.</li>
</ul>
<h4>Not available for return:</h4>
<ul>
<li>No returns for software delivered by email.</li>
</ul>
<p></p>
<h3>Books, Movies, Music and Video Games</h3>
<h4>Must be returned within 90 days if unused, unopened and unmarked</h4>
<ul>
<li>Books must be returned unused and unmarked.</li>
<li>CDs, DVDs, Blu-ray discs, audiotapes, videotapes and video games must be returned unopened. If the item is defective, it can be returned within 90 days with a receipt and the original packaging. Defective items may be exchanged for the same title.</li>
<li>Video on Demand cannot be returned. All sales are final and all charges from those sales are nonrefundable</li>
<li>Video game software, if defective, can be returned within 90 days with a receipt and the original packaging. Defective items may be exchanged for a different title if the same title is not available.</li>
</ul>
<h4>Must be returned within 15 days</h4>
<ul>
<li>Pre-owned (refurbished) video game hardware must be returned within 15 days of receipt</li>
</ul>
<h2>Refund timelines</h2>
<p>Once we accept and process your return, please allow about one to two business days for a merchandise Credit to appear in your Your Store account. If you chose to make your return for a refund, you will see the amount reflected on your original form of payment within seven business days.</p>
<p><span style="font-size: large; background-color: #ffff00; color: #ff0000;"><strong>Please note this is an example of RMA Policy! It should be changed before you start using the RMA functionality!</strong></span></p>
TEXT;

            $blockData = [
                Block::IDENTIFIER   => $blockIdentifier,
                Block::TITLE        => 'RMA Return Policy',
                Block::CONTENT      => $content,
                Block::IS_ACTIVE    => true,
                'stores' => [0],
            ];

            $block->setData($blockData)->save();
        }
    }

    /**
     * Create success page CMS block
     *
     * @return void
     */
    private function createSuccessPageBlock()
    {
        $block = $this->blockFactory->create();
        $blockIdentifier = 'prrma_success_page';
        $block->setStoreId(0)->load($blockIdentifier);

        if (! $block->getId()) {
            $content = '<p>Your return has been received and is being processed. Our team will contact you shortly.</p>';

            $blockData = [
                Block::IDENTIFIER   => $blockIdentifier,
                Block::TITLE        => 'RMA Success Page',
                Block::CONTENT      => $content,
                Block::IS_ACTIVE    => true,
                'stores' => [0],
            ];

            $block->setData($blockData)->save();
        }
    }

    /**
     * Create return instructions CMS block
     *
     * @return void
     */
    private function createReturnInstructionsBlock()
    {
        $block = $this->blockFactory->create();
        $blockIdentifier = 'prrma_instructions';
        $block->setStoreId(0)->load($blockIdentifier);

        if (! $block->getId()) {
            $content = <<<TEXT
<div style="background: #fff9e5; border: 1px solid #ded4b2; border-radius: 5px; padding: 8px;">
<h3>Congratulations! Your Return Request is Approved</h3>
<p><strong>If you wish to return an item to yourdomain.com, please follow the instructions below:</strong></p>
<img style="border: 1px solid #e4d6a8; border-radius: 5px; max-width: 100%;" src="{{view url="Plumrocket_RMA::images/instructions_icons.jpg"}}" alt="" />
<p>1. Print the packing slip and shipping label simply by clicking the buttons below. <br />{{block class="Plumrocket\RMA\Block\Returns\Buttons" name="prrma-instructions-buttons" template="Plumrocket_RMA::returns/instructions/buttons.phtml"}}</p>
<p>2. Pack the item(s) securely in the original product packaging, if possible. All items must be returned in good condition to ensure that you receive credit. Before sending your return shipment, please remove all extra labels from the outside of the package. Now add the printed packing slip into your package.</p>
<p>3. Attach the printed shipping label on your package.</p>
<p>4. The package should be shipped pre-paid through a traceable method like UPS or Insured Parcel Post. Please note: Shipping and Handling costs, gift box costs and other charges are non-refundable.</p>
</div>
TEXT;

            $blockData = [
                Block::IDENTIFIER   => $blockIdentifier,
                Block::TITLE        => 'RMA Return Instuctions',
                Block::CONTENT      => $content,
                Block::IS_ACTIVE    => true,
                'stores' => [0],
            ];

            $block->setData($blockData)->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $blockIds = ['prrma_policy', 'prrma_success_page', 'prrma_instructions'];

        foreach ($blockIds as $blockId) {
            $block = $this->blockFactory->create();
            $block->setStoreId(0)->load($blockId);

            if ($block->getId()) {
                $block->delete();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
