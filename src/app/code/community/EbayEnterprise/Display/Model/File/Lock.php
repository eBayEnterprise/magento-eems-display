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
 * Ignoring code coverage for this class because it is mainly dealing with creating lock file, checking if lock file exists, and deleting lock file.
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Model_File_Lock
{
	const FILE_OPEN_MODE = 'a';
	const TIME_FORMAT = 'Y-m-d H:i:s';
	const EXCEPTION_DELETE_LOCK_FILE_MESSAGE = 'Can not remove feed lock file';
	const EXCEPTION_LOCK_FILE_EXIST_MESSAGE = 'Can not generate feed because lock file already exits';
	/** @var EbayEnterprise_Display_Helper_Config $_config */
	protected $_config;
	/** @var EbayEnterprise_Display_Helper_Data $_helper */
	protected $_helper;
	/** @var SplFileInfo $_stream */
	protected $_stream;
	/** @var string $_lastRunDateTime */
	protected $_lastRunDateTime;
	/** @var Mage_Core_Model_Date $_date */
	protected $_date;
	/** @var array $_notice */
	protected $_notice;

	public function __construct()
	{
		$this->_config = Mage::helper('eems_display/config');
		$this->_helper = Mage::helper('eems_display');
		$this->_date = Mage::getModel('core/date');
		$this->_stream = new SplFileInfo($this->_config->getFeedFilePath() . DS . $this->_config->getFeedLockFilename());
		$this->_lastRunDateTime = $this->_date->date(static::TIME_FORMAT);
		$this->_notice = $this->_config->getFeedAdminNotice();
	}
	/**
	 * Check if the product feed lock file exist.
	 * @return bool true if the file exist otherwise false
	 */
	protected function _isFeedLockFileExist()
	{
		return $this->_stream->isFile();
	}
	/**
	 * Create a file if is not already exists.
	 * @return self
	 */
	protected function _createFeedLockFile()
	{
		if (!$this->_isFeedLockFileExist()) {
			$this->_stream->openFile(static::FILE_OPEN_MODE);
		}
		return $this;
	}
	/**
	 * Get the last run date time configuration data to be used to update last run date in the
	 * 'core_config_data' table.
	 * @return array
	 */
	protected function _getLastRunDateConfigData()
	{
		return array(
			'path' => EbayEnterprise_Display_Helper_Config::EEMS_DISPLAY_PRODUCT_FEED_LAST_RUN_DATETIME,
			'value' => $this->_lastRunDateTime,
			'scope' => 'default',
			'scope_id' => 0,
		);
	}
	/**
	 * Calculating the time it took in minutes to successfully run the product feed by using the
	 * the last run date time from configuration and the current time now.
	 * @return float
	 */
	protected function _calculateDuration()
	{
		$interval = $this->_helper->getDateInterval($this->_config->getFeedLastRunDatetime(), $this->_date->date(static::TIME_FORMAT));
		return $this->_helper->calculateTimeElapseInMinutes($interval);
	}
	/**
	 * Get the last run duration configuration data to be used to update last run duration in the
	 * 'core_config_data' table.
	 * @return array
	 */
	protected function _getLastRunDurationConfigData()
	{
		return array(
			'path' => EbayEnterprise_Display_Helper_Config::EEMS_DISPLAY_PRODUCT_FEED_LAST_RUN_DURATION,
			'value' => $this->_calculateDuration(),
			'scope' => 'default',
			'scope_id' => 0,
		);
	}
	/**
	 * Save configuration data.
	 * @param array $data
	 * @return self
	 */
	protected function _saveConfig(array $data)
	{
		Mage::getModel('core/config_data')->addData($data)->save();
		return $this;
	}
	/**
	 * Delete lock file.
	 * @return self
	 * @throws EbayEnterprise_Display_Model_File_Exception
	 */
	protected function _deleteLockFile()
	{
		@unlink($this->_stream->getRealPath());
		if ($this->_isFeedLockFileExist()) {
			throw Mage::exception('EbayEnterprise_Display_Model_File', static::EXCEPTION_DELETE_LOCK_FILE_MESSAGE);
		}
		return $this;
	}
	/**
	 * Remove the feed lock file and save the last run duration in configuration.
	 * @return self
	 */
	public function doLockFileRemoval()
	{
		$this->_deleteLockFile()
			->_saveConfig($this->_getLastRunDurationConfigData());
		return $this;
	}
	/**
	 * This is the entry point to check if lock file already exist to throw an exception to prevent product feed
	 * processing from running. If lock file doesn't exist yet, simply save the last date time feed ran and create the lock file.
	 * @return self
	 * @throws EbayEnterprise_Display_Model_File_Lock_Exception
	 */
	public function doLockFileCheck()
	{
		if ($this->_isFeedLockFileExist()) {
			throw Mage::exception('EbayEnterprise_Display_Model_File_Lock', static::EXCEPTION_LOCK_FILE_EXIST_MESSAGE);
		}
		$this->_createFeedLockFile();
		$this->_saveConfig($this->_getLastRunDateConfigData());
		return $this;
	}
	/**
	 * Process lock file removal from administrative backend.
	 * @return array
	 */
	public function processLockFileRemoval()
	{
		try {
			$this->_deleteLockFile();
		} catch (EbayEnterprise_Display_Model_File_Exception $e) {
			return array('message' => $e->getMessage(), 'success' => false);
		}
		return array('message' => $this->_notice['remove_lock_file_text'], 'success' => true);
	}
	/**
	 * Get the remove lock file button text
	 * @return string
	 */
	public function getRemoveLockButonText()
	{
		return $this->_notice['lock_file_exist_button_text'];
	}
	/**
	 * Get lock file exists text
	 * @return string
	 */
	public function getLockFileExistText()
	{
		return $this->_notice['lock_file_exist_text'];
	}
	/**
	 * Get the fail to remove lock file text from configuration.
	 * @return string
	 */
	public function getFailRemoveLockFileText()
	{
		return $this->_notice['fail_to_remove_lock_file_text'];
	}
	/**
	 * Check if lock file exists in order to show the lock file removal button in the backend interface.
	 * @return bool
	 */
	public function isLockFileExist()
	{
		return $this->_isFeedLockFileExist();
	}
}
