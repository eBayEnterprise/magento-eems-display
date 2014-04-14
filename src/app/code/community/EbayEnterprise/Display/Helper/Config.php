<?php
class EbayEnterprise_Display_Helper_Config extends Mage_Core_Helper_Abstract
{
	const EEMS_DISPLAY_CONFIG_ENABLED          = 'marketing_solutions/eems_display/enabled';
	const EEMS_DISPLAY_CONFIG_SITE_ID          = 'marketing_solutions/eems_display/site_id';
	const EEMS_DISPLAY_CONFIG_PRODUCT_FEED_URL = 'marketing_solutions/eems_display/product_feed_url';
	/**
	 * Get whether or not this extension is enabled.
	 * @return boolean
	 */
	public function isEnabled($storeId)
	{
		return Mage::getStoreConfigFlag(self::EEMS_DISPLAY_CONFIG_ENABLED, $storeId);
	}
	/**
	 * Get the SiteId from admin configuration.
	 * @return string
	 */
	public function getSiteId($storeId)
	{
		return Mage::getStoreConfig(self::EEMS_DISPLAY_CONFIG_SITE_ID, $storeId);
	}
	/**
	 * Get the Product Feed URL
	 * @return string
	 */
	public function getProductFeedUrl($storeId)
	{
		return Mage::getStoreConfig(self::EEMS_DISPLAY_CONFIG_PRODUCT_FEED_URL, $storeId);
	}
}