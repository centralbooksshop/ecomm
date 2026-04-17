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
namespace Webkul\DeliveryBoy\Model\Deliveryboy\Validator;

class CompositeValidator implements \Laminas\Validator\ValidatorInterface
{
    /**
     * @var \Laminas\Validator\ValidatorInterface[]
     */
    private $validators;

    /**
     * Validation error messages
     *
     * @var array
     */
    private $_messages = [];

    /**
     * @param array $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * Check whether the entity is valid according to defined validation rules
     *
     * @param \Magento\Framework\DataObject $entity
     * @return bool
     *
     * @throws \Exception
     * @api
     */
    public function isValid($entity)
    {
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($entity)) {
                $this->_messages += $validator->getMessages();
            }
        }
        $this->_messages = array_unique($this->_messages);
        
        return empty($this->_messages);
    }

    /**
     * Return error messages (if any) after the last validation
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
