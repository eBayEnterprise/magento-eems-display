<?php
class EbayEnterprise_Display_Model_Adminhtml_System_Config_Backend_Feedurl
	extends Mage_Core_Model_Config_Data
{
	const SESSION_KEY = 'adminhtml/session';
	/**
	 * Return store Id for the current configuration scope
	 * @return string
	 */
	public function getStoreIdForCurrentScope()
	{
		$storeCode = Mage::app()->getRequest()->getParam('store');
		if (!empty($storeCode)) {
			return Mage::getModel('core/store')->load($storeCode)->getId();
		}

		$websiteCode = Mage::app()->getRequest()->getParam('website');
		if (empty($websiteCode)) {
			return Mage::app()->getDefaultStoreView()->getId();
		}

		$websiteId = Mage::getModel('core/website')->load($websiteCode)->getId();
		return Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
	}
	/**
	 * Take the current configuration view and append the Display Feed frontName
	 * to present a complete route
	 */
	public function _afterLoad()
	{
		parent::_afterLoad();
		$sess    = Mage::getSingleton($this::SESSION_KEY);
		$storeId = $this->getStoreIdForCurrentScope();
		$siteId  = Mage::helper('eems_display/config')->getSiteId($storeId);
		if (empty($siteId)) {
			$this->setValue('');
			$sess->addWarning('No Site Id configured for current scope.');
		} else {
			$this->setValue(Mage::helper('eems_display')->getProductFeedUrl($storeId));
		}
		return $this;
	}
}
