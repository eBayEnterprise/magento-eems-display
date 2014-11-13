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

/**
 * Ignoring code coverage for this class because all it does is observed events and call the proper method
 * to process whatever is needed to be processed.
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Model_Observer
{
	/** @var EbayEnterprise_Display_Model_Products $_feed */
	protected $_feed;
	/** @var EbayEnterprise_Display_Model_File_Lock $_lock */
	protected $_lock;

	public function __construct()
	{
		$this->_feed = Mage::getModel('eems_display/products');
		$this->_lock = Mage::getModel('eems_display/file_lock');
	}
	/**
	 * This observer method is the entry point to generating product feed when
	 * the CRONJOB 'eems_display_products_feed' run.
	 * @return self
	 */
	public function generateProductFeed()
	{
		$this->_feed->export();
		return $this;
	}
	/**
	 * This observer method is observing the event 'eems_display_generate_product_feed_before' in order
	 * to process lock file check before processing the product feed.
	 * @return self
	 */
	public function runCheckLockFile()
	{
		$this->_lock->doLockFileCheck();
		return $this;
	}
	/**
	 * This observer method is observing the event 'eems_display_generate_product_feed_after' in order
	 * to process lock file removal after the product feed has successfully been generated.
	 * @return self
	 */
	public function runLockFileRemoval()
	{
		$this->_lock->doLockFileRemoval();
		return $this;
	}
}
