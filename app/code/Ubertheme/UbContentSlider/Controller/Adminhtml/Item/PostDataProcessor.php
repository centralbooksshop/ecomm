<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

use Magento\Framework\Stdlib\DateTime\Timezone;

class PostDataProcessor
{
    /**
     * @var Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * PostDataProcessor constructor.
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
    ) {
        $this->timezone = $timezone;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    public function filter($data) {
        $inputFilter = new \Zend_Filter_Input(
            [],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();

        //convert locale datetime values to UTC datetime
        if (!empty($data['start_time'])) {
            $datetime = new \DateTime($data['start_time'], new \DateTimeZone($this->timezone->getConfigTimezone()));
            $data['start_time'] = $this->timezone->convertConfigTimeToUtc($datetime, 'Y-m-d H:i');
        }
        if (!empty($data['end_time'])) {
            $datetime = new \DateTime($data['end_time'], new \DateTimeZone($this->timezone->getConfigTimezone()));
            $data['end_time'] = $this->timezone->convertConfigTimeToUtc($datetime, 'Y-m-d H:i');
        }

        return $data;
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool Return FALSE if someone item is invalid
     */
    public function validate($data){
        return true;
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'title' => __('Item Title'),
            'is_active' => __('Status')
        ];
        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->messageManager->addError(
                    __('To apply changes you should fill in hidden required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }
}
