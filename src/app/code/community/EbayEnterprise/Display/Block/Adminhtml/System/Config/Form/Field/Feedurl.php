<?php
/**
 * This class is block (but is specified as a 'frontend_model' it the config.xml)
 * which effectively overrides lib/Varien/Data/Form/Element/Textarea.php so that we
 * can special case what we wish to display.
 *
 * Consult Varien_Data_Form_Element_Abstract for other methods available in the '$element'
 */
class EbayEnterprise_Display_Block_Adminhtml_System_Config_Form_Field_Feedurl
	extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$element->addClass('link');
		$id = $element->getHtmlId();

		// If there's an inherited checkbox; hide it. This is a read-only property, and the checkbox is superfluous
		$inheritedCheckbox = $id . '_inherit';
		$hideCheckboxJs    = 'if ($("' . $inheritedCheckbox . '") != undefined ){$("' . $inheritedCheckbox . '").hide();}';
		$html = '<a id="' . $id . '" name="'.$element->getName()
			. '" onClick="return false;" href="#">' .  $element->getEscapedValue() . '</a>'
			. Mage::helper('core/js')->getScript('document.observe("dom:loaded", function() {' . $hideCheckboxJs . '});');
		return $html;
	}
}