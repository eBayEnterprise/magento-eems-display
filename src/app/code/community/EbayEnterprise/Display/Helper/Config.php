<?php
class EbayEnterprise_Display_Helper_Data extends Mage_Core_Helper_Abstract
{
	const EEMS_DISPLAY_CONFIG_ENABLED          = 'marketing_solutions/eems_display/enabled';
	const EEMS_DISPLAY_CONFIG_SITE_ID          = 'marketing_solutions/eems_display/merchant_id';
	const EEMS_DISPLAY_CONFIG_PRODUCT_FEED_URL = 'marketing_solutions/eems_display/ftp_host';
	/**
	 * Get whether or not this extension is enabled.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return boolean
	 */
	public function isEnabled($storeView = null)
	{
		// @TODO Suspect code, getSiteId was passing '$storeViewId', which was an undefined variable
		return (
			Mage::getStoreConfigFlag(self::EEMS_DISPLAY_CONFIG_ENABLED, $storeView) &&
			'' !== $this->getSiteId($storeView)
		);
	}
	/**
	 * Get the FetchBack merchant id from admin configuration.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getSiteId($storeView = null)
	{
		return Mage::getStoreConfig(self::EEMS_DISPLAY_CONFIG_SITE_ID, $storeView);
	}
	/**
	 * Get the Product Feed URL
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getProductFeedUrl($storeView = null)
	{
		return Mage::getStoreConfig(self::EEMS_DISPLAY_CONFIG_PRODUCT_FEED_URL, $storeView);
	}
}