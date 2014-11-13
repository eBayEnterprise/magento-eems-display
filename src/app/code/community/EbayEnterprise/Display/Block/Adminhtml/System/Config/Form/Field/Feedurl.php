<?php
/**
 * Copyright (c) 2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the eBay Enterprise
 * Magento Extensions End User License Agreement
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf
 *
 * @copyright   Copyright (c) 2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf  eBay Enterprise Magento Extensions End User License Agreement
 *
 */

/**
 * This class is block (but is specified as a 'frontend_model' it the config.xml)
 * which effectively overrides lib/Varien/Data/Form/Element/Textarea.php so that we
 * can special case what we wish to display.
 *
 * Consult Varien_Data_Form_Element_Abstract for other methods available in the '$element'
 */
/**
 * Ignoring code coverage for this class because it is a block class containing methods for displaying
 * feed URL, message about the date/time the feed was last run, how long it took to run, message about
 * feed never run if the feed has never ran since the install of this extension
 * and give the ability to remove lock file if a lock file currently exists in the feed directory.
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Block_Adminhtml_System_Config_Form_Field_Feedurl
	extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	const REMOVE_LOCK_FILE_CONTROLLER = '*/eemsdisplay_system_config_lock/removelockfile';
	const FEED_URL_JS = 'eems_display/system/config/feedurl.js';
	/** @var string $_template */
	protected $_template = 'eems_display/system/config/feedurl.phtml';
	/** @var  EbayEnterprise_Display_Helper_Data $_helper */
	protected $_helper;
	/** @var  EbayEnterprise_Display_Model_File_Feed $_feed */
	protected $_feed;
	protected function _construct()
	{
		parent::_construct();
		$this->_helper = Mage::helper('eems_display');
		$this->_feed = Mage::getModel('eems_display/file_feed');
	}
	/**
	* Include the feed URL JavaScript file.
	* @see Mage_Core_Block_Abstract::_prepareLayout()
	*/
	protected function _prepareLayout()
	{
		$this->getLayout()->getBlock('head')->addJs(static::FEED_URL_JS);
		parent::_prepareLayout();
	}
	/**
	 * Unset some non-related element parameters
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
		return parent::render($element);
	}
	/**
	 * Determine the run text.
	 * @return string
	 */
	public function getLastRunText()
	{
		return $this->_feed->hasFeedRan() ? $this->_getLastRunFormattedText() : $this->_helper->__($this->_feed->getNeverRunText());
	}
	/**
	 * Get last run text concatenated with the last run duration text.
	 * @return string
	 */
	protected function _getLastRunFormattedText()
	{
		return sprintf('%s<br />%s', $this->_helper->__($this->_feed->getLastRunDateText()), $this->_helper->__($this->_feed->getLastRunDurationText()));
	}
	/**
	 * Get the feed URL contents which include information about the last time a feed was generate, lock file information and scripts contents
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$element->addClass('link');
		$lock = Mage::getModel('eems_display/file_lock');
		$this->addData(array(
			'name' => $element->getName(),
			'escaped_value' => $element->getEscapedValue(),
			'is_lock_file_exist' => $lock->isLockFileExist(),
			'lock_file_exist_text' => $this->_helper->__($lock->getLockFileExistText()),
			'fail_to_remove_lock_file_text' => $this->_helper->__($lock->getFailRemoveLockFileText()),
			'escaped_button_label' => $this->_helper->__($this->escapeHtml($lock->getRemoveLockButonText())),
			'html_id' => $element->getHtmlId(),
			'ajax_url' => $this->getUrl(static::REMOVE_LOCK_FILE_CONTROLLER, array('_current' => true))
		));

		return $this->_toHtml();
	}
}
