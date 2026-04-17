<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Block\Adminhtml\Item\Edit\Tab;

/**
 * UBCS slide item edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->timezone = $timezone;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Ubertheme\UbContentSlider\Model\Item */
        $model = $this->_coreRegistry->registry('ubcontentslider_item');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Ubertheme_UbContentSlider::item_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('item_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Basic Information')]);

        if ($model->getId()) {
            $fieldset->addField('item_id', 'hidden', ['name' => 'item_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Slide Item Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'slide_id',
            'select',
            [
                'label' => __('Show In Slider'),
                'title' => __('Select One Slider'),
                'name' => 'slide_id',
                'required' => true,
                'options' => $model->getSliderOptions(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'link',
            'text',
            [
                'name' => 'link',
                'label' => __('Link'),
                'title' => __('Link of slide item'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'target',
            'select',
            [
                'name' => 'target',
                'label' => __('Link Target'),
                'title' => __('Select target of slide item\'s link'),
                'required' => true,
                'options' => $model->getLinkTargetOptions(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'content_type',
            'select',
            [
                'name' => 'content_type',
                'label' => __('Content Type'),
                'title' => __('Select content type of slide item'),
                'required' => true,
                'options' => $model->getContentTypeOptions(),
                'onchange' => 'showHideContentFields(this);',
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'video_id',
            'text',
            [
                'name' => 'video_id',
                'label' => __('Video ID'),
                'title' => __('The ID of video (Support: Youtube and Vimeo video)'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addType('image', 'Ubertheme\UbContentSlider\Block\Adminhtml\Item\Helper\Image');
        $imageNote = __('Upload an image for your video cover. If not specified, the cover image of exiting video will be used.');
        $imageNote .= '&nbsp;'.__('Allowed file types: jpg, jpeg, gif, png');
        $fieldset->addField(
            'video_cover',
            'image',
            array(
                'name' => 'video_cover',
                'label' => __('Video Cover'),
                'title' => __('Select an image for your video cover'),
                'note' => $imageNote,
                'required' => false,
                'class' => 'slide-image',
                'disabled' => $isElementDisabled
            )
        );

        $fieldset->addType('image', 'Ubertheme\UbContentSlider\Block\Adminhtml\Item\Helper\Image');
        $imageNote = __('Add the main image that you want to display.');
        $imageNote .= '&nbsp;'.__('Allowed file types: jpg, jpeg, gif, png');
        $fieldset->addField(
            'image',
            'image',
            array(
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Select an Image to upload'),
                'note' => $imageNote,
                'required' => false,
                'class' => 'slide-image',
                'disabled' => $isElementDisabled
            )
        );

        $fieldset->addType('image', 'Ubertheme\UbContentSlider\Block\Adminhtml\Item\Helper\Image');
        $mImageNote = __('If added, the image will be displayed instead of the main image for mobile devices.');
        $mImageNote .= '&nbsp;' . __('When enabling Image Hotspot, make sure Mobile Image\'s image ratio must be the same with that of the main image.');
        $mImageNote .= '&nbsp;' . __('Allowed file types: jpg, jpeg, gif, png');
        $fieldset->addField(
            'mobile_image',
            'image',
            array(
                'name' => 'mobile_image',
                'label' => __('Mobile Image'),
                'title' => __('The Mobile Image to upload'),
                'note' => $mImageNote,
                'required' => false,
                'class' => 'slide-image',
                'disabled' => $isElementDisabled
            )
        );

        /** @var \Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser $productChooser */
        $productChooser = $this->_layout->createBlock('\Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser');
        $configChooser = [
            'button' => [
                'open' => __('Search Products'),
                'type' => '\Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser',
            ]
        ];
        $productChooser->setConfig($configChooser);
        $productChooserHTML = '<div id="product-chooser-grid" class="product-chooser-grid" style="margin: 10px; display: none;">';
        $productChooserHTML .= $productChooser->toHtml();
        $productChooserHTML .= ' </div>';

        if ($model->getContentType() == 'image' && $model->getImage()) {
            $fieldset->addType(
                'hotSpot',
                '\Ubertheme\UbContentSlider\Block\Adminhtml\Item\Edit\Renderer\HotSpot'
            );
            $hotSpots = trim($model->getHotSpots());
            $hotSpots = !empty($hotSpots) ? $hotSpots : '[]';
            $fieldset->addField('hot_spot', 'hotSpot', [
                'name' => 'hot_spot',
                'label' => __('Add Hotspot'),
                'title' => __('Hotspot Editor'),
                'style' => 'display:none;',
                'disabled' => $isElementDisabled,
                'imgUrl' => $model->getImage(),
                'after_element_html' => $productChooserHTML,
                'after_element_js' => '
                    <script type="text/x-magento-init">
                    {
                        "#hs-main-panel": {
                            "Ubertheme_UbContentSlider/js/ub-hotspot": {
                                "dataPlaceholder": "#'. $form->getHtmlIdPrefix() . 'hot_spot' .'",
                                "dataAdded":' . $hotSpots . ',
                                "baseUrl":"' . $this->getBaseUrl() .'",
                                "skuValidateUrl": "' . $this->getUrl('ubcontentslider/item/ajaxValidateSku') . '"
                            }
                        }
                    }
                    </script>
                '
            ]);
        }

        if($model->getData('start_time')) {
            $datetime = new \DateTime($model->getData('start_time'), new \DateTimeZone('UTC'));
            $datetime = $this->timezone->date($datetime, null, $this->_localeDate->getConfigTimezone());
            $model->setData('start_time', $datetime);
        }
        if($model->getData('end_time')) {
            $datetime = new \DateTime($model->getData('end_time'), new \DateTimeZone('UTC'));
            $datetime = $this->timezone->date($datetime, null, $this->_localeDate->getConfigTimezone());
            $model->setData('end_time', $datetime);
        }

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
        $style = 'color: #000;background-color: #fff;font-weight:bold;font-size:13px;';
        $btnReset = '<a class="btn-clear-date" title="'.__('Click to clear current value').'" onclick="javascript:void(0);" style="cursor: pointer">' . __('Clear') . '</a>';
        $fieldset->addField(
            'start_time',
            'date',
            [
                'name' => 'start_time',
                'label' => __('Publish Time'),
                'title' => __('Starting publish time'),
                //'required' => true,
                //'class' => 'required-entry',
                'readonly' => true,
                'style' => $style,
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT)." | {$btnReset}",
            ]
        );

        $fieldset->addField(
            'end_time',
            'date',
            [
                'name' => 'end_time',
                'label' => __('End Time'),
                'title' => __('Ending publish time'),
                //'required' => true,
                //'class' => 'required-entry',
                'readonly' => true,
                'style' => $style,
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT)." | {$btnReset}",
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description Of Slide Item'),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Slide Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'class' => 'validate-not-negative-number'
            ]
        );

        $fieldset->addField(
            'additional_class',
            'text',
            [
                'name' => 'additional_class',
                'label' => __('Extra CSS Class'),
                'title' => __('The additional CSS class to control the style of the slide item.'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        if($model->getData('image')) {
            $model->setData('image', $model->getData('image'));
        }

        if($model->getData('mobile_image')) {
            $model->setData('mobile_image', $model->getData('mobile_image'));
        }

        $this->_eventManager->dispatch('adminhtml_ubcontentslider_item_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Basic Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Basic Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
