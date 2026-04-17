<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Block\Adminhtml\Item\Edit\Renderer;

use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Escaper;
use Ubertheme\UbContentSlider\Model\Item\Image as ImageModel;

/**
 * HotSpot hotSpot field renderer
 */
class HotSpot extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * image model
     *
     * @var \Ubertheme\UbContentSlider\Model\Item\Image
     */
    protected $imageModel;

    /**
     * HotSpot constructor.
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param ImageModel $imageModel
     * @param array $data
     */
    public function __construct(
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        ImageModel $imageModel,
        $data = []
    )
    {
        $this->imageModel = $imageModel;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Get the after element html.
     *
     * @return mixed
     */
    public function getAfterElementHtml()
    {
        $imgUrl = $this->imageModel->getBaseUrl().$this->getData('imgUrl');

        $hotSpotEditor = '<div id="hs-main-panel">';
            $hotSpotEditor .= '<div id="hs-editor">';
                $hotSpotEditor .= '<label>' . __('Drag to resize the hotspot') . '</label>';
                $hotSpotEditor .= '<div id="hs-slider"></div>';
                $hotSpotEditor .= '<div id="image-panel">';
                    $hotSpotEditor .= '<img id="image-map" src="'.$imgUrl.'" />';
                    $hotSpotEditor .= '<div id="mapper"></div>';
                    $hotSpotEditor .= '<div id="added-hot-spots"></div>';
                    $hotSpotEditor .= $this->getHotSpotForm();
                $hotSpotEditor .= '</div>';
            $hotSpotEditor .= '</div>';
        $hotSpotEditor .= '</div>';
        $hotSpotEditor .= $this->getData('after_element_html');

        return $hotSpotEditor;
    }

    protected function getHotSpotForm() {
        $form = '<div id="hs-form" class="hot-spot-form">
                <div class="row">
                    <div class="label" title="'.__("Product's SKU").'">' . __("Product's SKU") . '
                    <a id="btn-search-sku" title="'. __("Search by ID, SKU, Name") .'" class="btn-search-sku">
                    ' .__("Search") . '</a> 
                    </div>
                    <div class="field">
                        <input id="product-sku" class="sku" type="text" placeholder="sku" />
                    </div>
                </div>
                <div class="row">
                    <div class="label"></div>
                    <div class="field">
                        <div class="actions">
                            <a id="btn-cancel-hot-spot" href="javascript:void(0);">'.__("Cancel").'</a>
                            <input id="btn-save-hot-spot" class="btn-save-hot-spot" type="button" value="'.__("Add").'"/>
                        </div>
                    </div>
                </div>
            </div>';

        return $form;
    }
}
