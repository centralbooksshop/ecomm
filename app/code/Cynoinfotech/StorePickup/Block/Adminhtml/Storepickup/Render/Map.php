<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Storepickup\Render;

use Magento\Backend\Block\AbstractBlock;

class Map extends AbstractBlock implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = [];
        $html[] = '<div class="admin__field field with-note">
						<label class="label admin__field-label"><span>'. __('Map') .'</span></label>
                        <div class="admin__field-control control">
                            <div style="width:600px;height:400px;" id="map-canvas" class="map-container"></div>
                        </div>
					</div>';
                    
        return implode('', $html);
    }
}
