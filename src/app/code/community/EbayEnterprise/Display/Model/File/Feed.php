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
 * Ignoring code coverage for this class because it is checking if product feed files exist in the file system.
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Model_File_Feed
{
	const LAST_RUN_DATE_TIME_FORMAT = 'm/d/Y h:i:s A';
	/** @var EbayEnterprise_Display_Helper_Config $_config */
	protected $_config;
	/** @var SplFileInfo $_stream */
	protected $_stream;
	/** @var array $_notice */
	protected $_notice;

	public function __construct()
	{
		$this->_config = Mage::helper('eems_display/config');
		$this->_stream = new SplFileInfo($this->_config->getFeedFilePath());
		$this->_notice = $this->_config->getFeedAdminNotice();
	}
	/**
	 * Check if the product feed directory is writable.
	 * @return bool true if the product feed directory is writable otherwise false
	 */
	public function isWritable()
	{
		return $this->_stream->isWritable();
	}
	/**
	 * Check if the product feed has ever ran.
	 * @return bool true if the file exist otherwise false
	 */
	public function hasFeedRan()
	{
		return $this->_stream->isDir() && $this->_hasFeedFiles();
	}
	/**
	 * Check if the feed directory has any feed files.
	 * @return bool true if the file exist otherwise false
	 */
	protected function _hasFeedFiles()
	{
		return count($this->_getProductFeedFiles()) > 0;
	}
	/**
	 * Get an array list of all the product feed files.
	 * @return array
	 */
	protected function _getProductFeedFiles()
	{
		return glob($this->_stream->getRealPath() . DS . '*' . EbayEnterprise_Display_Model_Products::CSV_FILE_EXTENSION);
	}
	/**
	 * Get the feed last run date time in a properly format string.
	 * @return string
	 */
	protected function _getFormatLastRunDatetime()
	{
		return Mage::getModel('core/date')->gmtDate(static::LAST_RUN_DATE_TIME_FORMAT, $this->_config->getFeedLastRunDatetime());
	}
	/**
	 * Get last run date time text.
	 * @return string
	 */
	public function getLastRunDateText()
	{
		return sprintf($this->_notice['last_run_date_text'], $this->_getFormatLastRunDatetime());
	}
	/**
	 * Get last run duration text.
	 * @return string
	 */
	public function getLastRunDurationText()
	{
		return sprintf($this->_notice['last_run_duration_text'], $this->_config->getFeedLastRunDuration());
	}
	/**
	 * Get feed never run text.
	 * @return string
	 */
	public function getNeverRunText()
	{
		return $this->_notice['never_run_text'];
	}
}
