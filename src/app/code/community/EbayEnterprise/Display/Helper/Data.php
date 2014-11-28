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

class EbayEnterprise_Display_Helper_Data extends Mage_Core_Helper_Abstract
{
	const EEMS_DISPLAY_PRODUCT_FEED_ROUTE = '/index/retrieve?id=';
	/**
	 * Gets the fully formed URL of the Product feed for this storeId.
	 * @return string uri for feed for this store
	 */
	public function getProductFeedUrl($storeId)
	{
		return Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) .
		Mage::helper('eems_display/config')->getProductFeedFrontName() . self::EEMS_DISPLAY_PRODUCT_FEED_ROUTE . $storeId;
	}
	/**
	 * Find the closest default store id for the current admin scope.
	 * @return string
	 */
	public function getStoreIdForCurrentAdminScope()
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
	 * Split site id checksum field.
	 * Element 0 holds the hash, Element 1 holds the original site id used to generate it.
	 */
	public function splitSiteIdChecksumField($field)
	{
		$sep = EbayEnterprise_Display_Model_Adminhtml_System_Config_Backend_Siteidchecksum::FIELD_SEP;
		if (strpos($field, $sep) === false) {
			return array('','');
		}
		return preg_split("/$sep/", $field);
	}
	/**
	 * Strip out Carriage Return (CR) and Line Feed (LF) characters from a string
	 * @param string $content
	 * @return string
	 */
	public function cleanString($content)
	{
		return trim(preg_replace("/\s*[\r\n]+\s*/", ' ', $content));
	}

	/**
	 * Clean all unwanted characters from a string in preparation for
	 * inclusion in the feed
	 *
	 * Strips CR, LF, HTML, and extra white space
	 * Accepts UTF-8 characters
	 *
	 * - Strip any HTML and PHP tags
	 * - Decode any HTML encoded characters (ie.: &agrave;)
	 * - Strip CR and LF and extra white space
	 *
	 * @param string $content
	 * @return string
	 */
	public function cleanStringForFeed($content)
	{
		$helper = Mage::helper('core');

		return $this->cleanString(html_entity_decode($helper->stripTags($content), ENT_COMPAT, 'UTF-8'));
	}

	/**
	 * Make a string value SQL safe
	 * @param string $value
	 * @return string
	 */
	public function makeSqlSafe($value)
	{
		return str_replace("'", "", Mage::getSingleton('core/resource')->getConnection('default_write')->quote($value));
	}

	/**
	 * @param string $url
	 * @return string absolute path to the file on the server
	 */
	public function getLocalPathFromUrl($url)
	{
		$info = parse_url($url);
		if (!$info || empty($info['host'])) {
			return $url;
		}

		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		if (strpos($url, $baseUrl) !== 0)
			return $url;

		$path = substr($url, strlen($baseUrl));
		return Mage::getBaseDir() . DIRECTORY_SEPARATOR . $path;
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	public function isLocalUrl($url)
	{
		$info = parse_url($url);
		$magento = parse_url(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));

		return ($info['host'] === $magento['host']);
	}

	/**
	 * @param string $filename file name or url for the image file
	 * @param int $width
	 * @param int $height
	 * @return bool
	 */
	public function isValidImage($filename, $width = 0, $height = 0)
	{
		if (filter_var($filename, FILTER_VALIDATE_URL)) {
			// it can be expensive to check a remote file so
			// just return and assume it is valid
			if (!$this->isLocalUrl($filename)) {
				return true;
			}

			$filename = $this->getLocalPathFromUrl($filename);
		}

		if (!is_file($filename)) {
			return false;
		}

		$imageInfo = @getimagesize($filename);

		// if $width or $height === 0 then just validate the file or url is an image
		if (!($width && $height) && $imageInfo) {
			return true;
		}


		return ($imageInfo && ($imageInfo[0] === $width) && ($imageInfo[1] === $height));
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @param int $storeId
	 * @return array
	 */
	public function imageSizeForFeed(Mage_Catalog_Model_Product $product, $storeId)
	{
		try {
			$config = Mage::helper('eems_display/config');

			$frameWidth = $config->getFeedImageWidth($storeId);
			$frameHeight = $config->getFeedImageHeight($storeId);

			$dstWidth = $frameWidth;
			$dstHeight = $frameHeight;

			$image = Mage::helper('catalog/image');
			$image->init($product, 'image');

			$imageWidth = $image->getOriginalWidth();
			$imageHeight = $image->getOriginalHeight();

			if (($frameWidth > $imageWidth) && ($frameHeight > $imageHeight)) {
				// image is smaller than the frame. Don't scale and return the original image size
				$dstWidth = $imageWidth;
				$dstHeight = $imageHeight;
			}
		} catch (Exception $e) {
			Mage::log("imageSizeForFeed error: {$e->getMessage()}");
		}

		return array(
			'width' => $dstWidth,
			'height' => $dstHeight
		);
	}

	/**
	 * Return a DateInterval instance between two dates.
	 * @param  string $startDate
	 * @param  string $endDate
	 * @return DateInterval
	 */
	public function getDateInterval($startDate, $endDate)
	{
		$timeZone = new DateTimeZone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));
		$newDate = new DateTime($startDate, $timeZone);
		return $newDate->diff(new DateTime($endDate, $timeZone));
	}
	/**
	 * Calculate time in difference in minutes from a passing DateInterval instance.
	 * @param  DateInterval $interval
	 * @return float
	 */
	public function calculateTimeElapseInMinutes(DateInterval $interval)
	{
		return (($interval->y * 365 * 24 * 60) + ($interval->m * 30 * 24 * 60) + ($interval->d * 24 * 60) + ($interval->h * 60) + $interval->i);
	}
}
