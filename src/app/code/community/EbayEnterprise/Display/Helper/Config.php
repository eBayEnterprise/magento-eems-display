<?php
/**
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Helper_Config extends Mage_Core_Helper_Abstract
{
	const EEMS_DISPLAY_ENABLED_PATH                      = 'marketing_solutions/eems_display/enabled';
	const EEMS_DISPLAY_FEED_FILE_PATH                    = 'marketing_solutions/eems_display/feed/file_path';
	const EEMS_DISPLAY_FEED_IMAGE_HEIGHT_PATH            = 'marketing_solutions/eems_display/feed/image/height';
	const EEMS_DISPLAY_FEED_IMAGE_WIDTH_PATH             = 'marketing_solutions/eems_display/feed/image/width';
	const EEMS_DISPLAY_FEED_IMAGE_KEEP_ASPECT_RATIO_PATH = 'marketing_solutions/eems_display/feed/image/keep_aspect_ratio';
	const EEMS_DISPLAY_PRODUCT_FEED_FRONTNAME_PATH       = 'frontend/routers/eems_display/args/frontName';
	const EEMS_DISPLAY_SITE_ID_PATH                      = 'marketing_solutions/eems_display/site_id';
	/**
	 * Get whether or not this extension is enabled.
	 * @return boolean
	 */
	public function getIsEnabled($storeId)
	{
		return Mage::getStoreConfigFlag(self::EEMS_DISPLAY_ENABLED_PATH, $storeId);
	}
	/**
	 * Get the SiteId from admin configuration.
	 * @return string
	 */
	public function getSiteId($storeId)
	{
		return Mage::getStoreConfig(self::EEMS_DISPLAY_SITE_ID_PATH, $storeId);
	}
	/**
	 * The Feed File path, files are created here and served from here. Path is created if it
	 * does not exist. Throws error if we can't create it, or we can't write it.
	 * @throws EbayEnterprise_Display_Model_Error_Exception
	 * @return type
	 */
	public function getFeedFilePath()
	{
		$path = Mage::getBaseDir('var') . DS . Mage::getStoreConfig(self::EEMS_DISPLAY_FEED_FILE_PATH);
		if (!file_exists($path) && !@mkdir($path,0777,true)) {
			throw new EbayEnterprise_Display_Model_Error_Exception('Cannot create specified path: ' . $path);
		} 
		if (!is_dir($path)) {
			throw new EbayEnterprise_Display_Model_Error_Exception('Specified path is not a directory: ' . $path);
		}
		if (!is_writable($path)) {
			throw new EbayEnterprise_Display_Model_Error_Exception('Specified path is not writeable: ' . $path);
		}
		return $path;
	}
	/**
	 * Gets the frontName of the router for the Display feed controller 
	 * @return string
	 */
	public function getProductFeedFrontName()
	{
		return Mage::getConfig()->getNode(self::EEMS_DISPLAY_PRODUCT_FEED_FRONTNAME_PATH);
	}
	/**
	 * Gets the height configuration for feed images 
	 * @return int
	 */
	public function getFeedImageHeight($storeId)
	{
		return (int)Mage::getStoreConfig(self::EEMS_DISPLAY_FEED_IMAGE_HEIGHT_PATH, $storeId);
	}
	/**
	 * Get the width configuration for feed images
	 * @return int
	 */
	public function getFeedImageWidth($storeId)
	{
		return (int)Mage::getStoreConfig(self::EEMS_DISPLAY_FEED_IMAGE_WIDTH_PATH, $storeId);
	}
	/**
	 * Get the flag whether to keep aspect ratio
	 * @return boolean
	 */
	public function getFeedImageKeepAspectRatio($storeId)
	{
		return (bool)Mage::getStoreConfig(self::EEMS_DISPLAY_FEED_IMAGE_KEEP_ASPECT_RATIO_PATH, $storeId);
	}
}
