<?php
class OpsWay_Pipeliner_Block_Adminhtml_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        if ($element->getHtmlId() === 'OpsWay_Pipeliner_general') {
            echo $this->_getInfo();
        }
        return parent::render($element);

    }

    protected function _getInfo() {
        $output = $this->_getStyle();
        $output .= '<div class="opsway-info">';
        $output .= '<p style="clear:both;">';
        $output .= '<h2>Contact <a style="color:#eb5e00;" href="mailto:support@opsway.com">OpsWay Support team</a> or visit <a style="color:#eb5e00;" href="http://opsway.com">opsway.com</a> for additional information</h2>';
        $output .= '</p>';
        $output .= '</div>';
        return $output;
    }

    protected function _getStyle() {
        $content = '<style>';
        $content .= '.opsway-info { border: 1px solid #cccccc; background: #e7efef; margin-bottom: 10px; padding: 10px; height: auto; }';
        $content .= '.opsway-info .opsway-logo { float: right; padding: 5px; }';
        $content .= '.opsway-info .opsway-command { border: 1px solid #cccccc; background: #ffffff; padding: 15px; text-align: left; margin: 10px 0; font-weight: bold; }';
        $content .= '.opsway-info h3 { color: #ea7601; }';
        $content .= '.opsway-info h3 small { font-weight: normal; font-size: 80%; font-style: italic; }';
        $content .= '</style>';
        return $content;
    }

}
