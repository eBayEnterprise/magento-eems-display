<?php
class EbayEnterprise_Display_Model_Adminhtml_System_Config_Backend_Feedurl
	extends Mage_Core_Model_Config_Data
{
	/**
	 * Take the current configuration view and append the Display Feed frontName
	 * to present a complete route
	 */
	protected function _afterLoad()
	{
		parent::_afterLoad();
		$storeId = Mage::helper('eems_display')->getStoreIdForCurrentAdminScope();
		$siteId  = Mage::helper('eems_display/config')->getSiteId($storeId);
		if (empty($siteId)) {
			$this->setValue('');
		} else {
			$this->setValue(Mage::helper('eems_display')->getProductFeedUrl($storeId));
		}
		return $this;
	}
}
