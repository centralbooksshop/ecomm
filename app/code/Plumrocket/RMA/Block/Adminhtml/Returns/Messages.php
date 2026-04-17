<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\RMA\Model\Response;
use Plumrocket\RMA\Model\Returns\Message;

class Messages extends Template
{
    /**
     * Backend auth
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Quick Response Template
     *
     * @var Response
     */
    protected $response;

    /**
     * @var Config
     */
    protected $wysiwygConfig;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param Context      $context
     * @param Auth         $auth
     * @param Response     $response
     * @param Config       $wysiwygConfig
     * @param UrlInterface $backendUrl
     * @param DateTime     $dateTime
     * @param array        $data
     */
    public function __construct(
        Context $context,
        Auth $auth,
        Response $response,
        Config $wysiwygConfig,
        UrlInterface $backendUrl,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->auth = $auth;
        $this->response = $response;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->backendUrl = $backendUrl;
        $this->dateTime = $dateTime;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve list of response templates
     *
     * @return Response[]
     */
    public function getResponseTemplates()
    {
        return $this->response
            ->getCollection()
            ->addActiveFilter()
            ->addStoreFilter($this->getOrder()->getStoreId())
            ->setOrder('title');
    }

    /**
     * Check if need to show mark as read
     *
     * @return bool
     */
    public function showMarkAsRead()
    {
        if ($this->isNewEntity()) {
            return false;
        }

        $entity = $this->getEntity();
        $lastMessage = $entity->getMessagesCollection()
            ->addFieldToFilter('is_internal', 0)
            ->addFieldToFilter('is_system', 0)
            ->setPageSize(1)
            ->getFirstItem();

        if (($entity->getId()
            && $entity->getReadMarkAt()
            && strtotime($entity->getReadMarkAt()) < strtotime($lastMessage->getCreatedAt()))
            || null === $entity->getReadMarkAt()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve url for mark as read
     *
     * @return string
     */
    public function getMarkAsReadUrl()
    {
        return $this->getUrl('*/returns/messages_markAsRead', [
            'id' => $this->getEntity()->getId(),
            'time' => $this->dateTime->gmtTimestamp(),
        ]);
    }

    /**
     * Retrieve url for load template
     *
     * @return string
     */
    public function getLoadTemplateUrl()
    {
        return $this->getUrl('*/returns/messages_loadTemplate');
    }

    /**
     * Retrieve messages list
     *
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->getEntity()->getMessages();
    }

    /**
     * Check if current user is the sender
     *
     * @param  Message $message
     * @return bool
     */
    public function isFromYou(Message $message)
    {
        switch ($message->getType()) {
            case Message::FROM_MANAGER:
                return $message->getFromId() == $this->auth->getUser()->getId();
        }

        return false;
    }

    /**
     * Get editor form element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getEditor()
    {
        return $this->createElement('returns_comment', 'editor', [
            'name'      => 'comment',
            'label'     => __('Comment (optional)'),
            'rows'      => 5,
            'config'    => $this->loadWysiwygConfig(),
            'value'     => $this->dataHelper->getFormData('comment'),
        ]);
    }

    /**
     * Get checkbox element of internal post
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getCheckboxOfInternal()
    {
        return $this->createElement('returns_comment_internal', 'checkbox', [
            'name'      => 'comment_is_internal',
            'label'     => __('Internal Post'),
            'value'     => '1',
            'checked'   => $this->dataHelper->getFormData('comment_is_internal'),
            'class'     => 'admin__control-checkbox',
        ]);
    }

    /**
     * Get checkbox element of email notification
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getCheckboxOfEmail()
    {
        return $this->createElement('returns_comment_send_email', 'checkbox', [
            'name'      => 'comment_send_email',
            'label'     => __('Notify Customer by Email'),
            'value'     => '1',
            'checked'   => $this->dataHelper->getFormData('comment_send_email') !== '0',
            'class'     => 'admin__control-checkbox',
        ]);
    }

    /**
     * Get wysiwyg config
     *
     * @return \Magento\Framework\DataObject
     */
    private function loadWysiwygConfig()
    {
        return $this->wysiwygConfig->getConfig([
            'directives_url' => $this->backendUrl->getUrl('cms/wysiwyg/directive'),
            'files_browser_window_url' => $this->backendUrl->getUrl('cms/wysiwyg_images/index'),
            'height' => '200px',
            'add_variables' => false,
            'add_widgets' => false,
        ]);
    }

    /**
     * Get file url
     *
     * @param string $filename
     * @return string
     */
    public function getFileUrl($filename)
    {
        return $this->returnsHelper->getFileUrl($this->getEntity(), $filename, true);
    }
}
