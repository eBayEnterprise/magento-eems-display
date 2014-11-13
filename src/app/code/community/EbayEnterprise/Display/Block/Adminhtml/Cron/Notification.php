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
 * Ignoring code coverage for this class because it is a block class containing methods for displaying
 * cron specific notification in the admin interface about cron not setup to run and feed directory writable permission.
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Block_Adminhtml_Cron_Notification extends Mage_Core_Block_Template
{
	const IS_CRON_RUNNING_MESSAGE = 'Cron is currently not running on this server.';
	const IS_FEED_DIRECTORY_WRITABLE_MESSAGE = 'The feed directory "%s" is not writable.';
	/** @var EbayEnterprise_Display_Helper_Data $_helper */
	protected $_helper;
	protected function _construct()
	{
		parent::_construct();
		$this->_helper = Mage::helper('eems_display');
	}
	/**
	 * Check if cron is disabled on this server.
	 * @return bool true cron running normally otherwise false
	 */
	public function isCronRunning()
	{
		return Mage::getModel('eems_display/cron')->isCronSetup();
	}
	/**
	 * Check if the product feed directory is writable.
	 * @return bool true the product directory is writable otherwise false
	 */
	public function isFeedDirectoryWritable()
	{
		return Mage::getModel('eems_display/file_feed')->isWritable();
	}
	/**
	 * The is cron running message to be displayed in the backend admin interface.
	 * @return string
	 */
	public function getIsCronRunningMessage()
	{
		return $this->_helper->__(static::IS_CRON_RUNNING_MESSAGE);
	}
	/**
	 * The is feed directory writable message to be displayed in the backend interface.
	 * @return string
	 */
	public function getIsFeedDirectoryWritableMessage()
	{
		return $this->_helper->__(sprintf(static::IS_FEED_DIRECTORY_WRITABLE_MESSAGE, Mage::helper('eems_display/config')->getFeedFileRelativePath()));
	}
}
