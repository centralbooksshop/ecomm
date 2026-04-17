<?php

namespace SchoolZone\Addschool\Block\Adminhtml\Timerange;


class Renderer extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Get the after element html.
     *
     * @return mixed
     */
    public function getElementHtml()
    {

        $data = $this->getSelectedShiftDetails();
        $html = '<table>
                 <tr>
                 <td class="toppadding">';
        $html .= '<select class="ds-mg" name="' . $this->getName() . '_from" id="delivery_minutes">';
        if (isset($data[0])) {
            $html .= $this->getHoursOptions(intval($data[0]));
        } else {
            $html .= $this->getHoursOptions(7);
        }
        $html .= '</select>';

        $html .= '<span class="toppadding ds-mg">-</span>';

        $html .= '<select class="ds-mg" name="' . $this->getName() . '_to" id="delivery_minutes">';
        if (isset($data[1])) {
            $html .= $this->getHoursOptions(intval($data[1]));
        } else {
            $html .= $this->getHoursOptions(22);
        }
        $html .= '</select>';

        $html .= '</td>
                  </tr>
                  </table>';
        $html .= '<style>';
        $html .= '.ds-mg{margin-right:4px;}';
        $html .= '</style>';
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    protected function getHoursOptions($val)
    {
        $html = '';
        for ($i = 0; $i < 24; $i++) {
            if ($val == $i) {
                $html .= '<option selected="selected">' . $i . '</option>';
            }
            else{
                $html .= '<option>' . $i . '</option>';
            }
        }
        return $html;
    }

    public function getSelectedShiftDetails()
    {
        $dataCollection = [];

        if ($this->getValues()) {
            $jsonData = json_decode($this->getValues(), true);
            $dataCollection = explode('-', $jsonData[$this->getName()]);
        }
        return $dataCollection;
    }
}