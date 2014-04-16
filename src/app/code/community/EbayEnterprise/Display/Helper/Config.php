<?php
/**
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Helper_Config extends Mage_Core_Helper_Abstract
{
	const EEMS_DISPLAY_CONFIG_ENABLED_PATH                = 'marketing_solutions/eems_display/enabled';
	const EEMS_DISPLAY_CONFIG_SITE_ID_PATH                = 'marketing_solutions/eems_display/site_id';
	const EEMS_DISPLAY_CONFIG_PRODUCT_FEED_FRONTNAME_PATH = 'frontend/routers/eems_display/args/frontName';
	/**
	 * Get whether or not this extension is enabled.
	 * @return boolean
	 */
	public function isEnabled($storeId)
	{
		return Mage::getStoreConfigFlag(self::EEMS_DISPLAY_CONFIG_ENABLED_PATH, $storeId);
	}
	/**
	 * Get the SiteId from admin configuration.
	 * @return string
	 */
	public function getSiteId($storeId)
	{
		return Mage::getStoreConfig(self::EEMS_DISPLAY_CONFIG_SITE_ID_PATH, $storeId);
	}
	/**
	 * Gets the frontName of the router for the Display feed controller 
	 * @return string
	 */
	public function getProductFeedFrontName()
	{
		return Mage::getConfig()->getNode(self::EEMS_DISPLAY_CONFIG_PRODUCT_FEED_FRONTNAME_PATH);
	}
}
