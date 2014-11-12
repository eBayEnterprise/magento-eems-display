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
	 * @param string
	 * @return string
	 */
	public function cleanString($content)
	{
		return trim(preg_replace("/\s*[\r\n]+\s*/", ' ', $content));
	}

	/**
	 * Strip all non-ascii characters from the string
	 * Removes all characters not in the range 0x20 - 0x7f
	 *
	 * @param string $content
	 * @return string
	 */
	public function stripNonAsciiChars($content)
	{
		return preg_replace('/[^(\x20-\x7E)]*/','', $content);
	}

	/**
	 * Clean all unwanted characters from a string in preparation for
	 * inclusion in the feed
	 *
	 * Strips CR, LF, HTML, non-ascii and extra white space
	 *
	 *
	 * - Strip any HTML and PHP tags
	 * - Converts accented characters into their unaccented equivalents
	 * - Decode any HTML encoded characters (ie.: &agrave;)
	 * - Strip all non-ascii characters
	 * - Strip CR and LF and extra white space
	 *
	 * @param string $content
	 * @return string
	 */
	public function cleanStringForFeed($content)
	{
		$helper = Mage::helper('core');
		return $this->cleanString($this->stripNonAsciiChars(html_entity_decode($helper->removeAccents($helper->stripTags($content)))));
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
}
