<?php
class EbayEnterprise_Display_Helper_Data extends Mage_Core_Helper_Abstract
{
	const EEMS_DISPLAY_PRODUCT_FEED_ROUTE = '/index/retrieve?id=';
	/**
	 * Gets the fully formed URL of the Product feed for this storeId.
	 * @return string uri for feed for this store
	 */
	public function getProductFeedUrl($storeId)
	{
		return Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)
			. Mage::helper('eems_display/config')->getProductFeedFrontName()
			. self::EEMS_DISPLAY_PRODUCT_FEED_ROUTE
			. $storeId;
	}
}
