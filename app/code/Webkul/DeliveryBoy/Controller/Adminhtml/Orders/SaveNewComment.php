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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Orders;

use Magento\Framework\Exception\LocalizedException;

class SaveNewComment extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\DeliveryBoy\Model\CommentFactory $commentF
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\DeliveryBoy\Model\CommentFactory $commentF,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->commentF = $commentF;
        $this->datetime = $datetime;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Save new Deliveryboy COmment.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $wholeData = $this->getRequest()->getParams();
        try {
            $comment = $wholeData["comment"] ?? "";
            $senderId = $wholeData["senderId"] ?? 0;
            $incrementId = $wholeData["incrementId"] ?? "";
            $isDeliveryboy = $wholeData["isDeliveryboy"] ?? false;
            $deliveryboyOrderId = $wholeData["deliveryboyOrderId"] ?? 0;

            if ($comment == "") {
                throw new LocalizedException(__("Comment field is required."));
            }
            if (str_word_count($comment < 5)) {
                throw new LocalizedException(__("Comment should be atleast 5 words."));
            }

            if ($senderId == 0) {
                $name = "Admin";
            }

            $this->commentF->create()->setComment($comment)
                ->setSenderId($senderId)
                ->setIsDeliveryboy($isDeliveryboy)
                ->setOrderIncrementId($incrementId)
                ->setDeliveryboyOrderId($deliveryboyOrderId)
                ->setCommentedBy($name)
                ->setCreatedAt($this->datetime->gmtDate())
                ->save();

            $result = $this->resultJsonFactory->create();
            return $result->setData(1);
        } catch (\Throwable $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(0);
        }
    }
}
