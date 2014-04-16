<?php
class EbayEnterprise_Display_Model_Adminhtml_System_Config_Backend_Feedurl
	extends Mage_Core_Model_Config_Data
{
	const SESSION_KEY = 'adminhtml/session';
	/**
	 * Return store Id for the current configuration scope
	 * @return string
	 */
	protected function _getStoreIdForCurrentScope()
	{
		$storeCode = Mage::app()->getRequest()->getParam('store');
		if (!empty($storeCode)) {
			return Mage::getModel('core/store')->load($storeCode)->getId();
		}

		$websiteCode = Mage::app()->getRequest()->getParam('website');
		if (empty($websiteCode)) {
			return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
		}

		$websiteId = Mage::getModel('core/website')->load($websiteCode)->getId();
		$storeId   = Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
		return $storeId;
	}
	/**
	 * Take the current configuration view and append the Display Feed frontName
	 * to present a complete route
	 */
	public function _afterLoad()
	{
		parent::_afterLoad();
		$productFeedUrl = '';

		$sess   = Mage::getSingleton($this::SESSION_KEY);
		$helper = Mage::helper('eems_display/config');

		$storeId   = $this->_getStoreIdForCurrentScope();
		$storeUrl  = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		$frontName = $helper->getProductFeedFrontName();

		$siteId = $helper->getSiteId($storeId);
		if (empty($siteId)) {
//			$sess->addWarning($helper->__("No Site Id configured for current scope."));
			$sess->addWarning("No Site Id configured for current scope.");
		} else {
			$productFeedUrl = $storeUrl . $frontName . '/index/retrieve?id=' . $storeId;
		}
		$this->setValue($productFeedUrl);
		return $this;
	}
}