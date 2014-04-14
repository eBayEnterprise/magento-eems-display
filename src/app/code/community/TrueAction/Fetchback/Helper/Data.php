<?php
/**
 * @category    TrueAction
 * @package     TrueAction_Fetchback
 * @copyright   Copyright (c) 2012 True Action Network (http://www.trueaction.com)
 */

class TrueAction_Fetchback_Helper_Data extends Mage_Core_Helper_Abstract
{

	const FETCHBACK_CONFIG_ENABLED     = 'advertising_marketing/fetchback/enabled';
	const FETCHBACK_CONFIG_MERCHANT_ID = 'advertising_marketing/fetchback/merchant_id';
	const FETCHBACK_CONFIG_FTP_HOST    = 'advertising_marketing/fetchback/ftp_host';
	const FETCHBACK_CONFIG_FTP_USER    = 'advertising_marketing/fetchback/ftp_username';
	const FETCHBACK_CONFIG_FTP_PASS    = 'advertising_marketing/fetchback/ftp_password';
	const FETCHBACK_CONFIG_FTP_PORT    = 'advertising_marketing/fetchback/ftp_port';

	/**
	 * Get whether or not this extension is enabled.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return boolean
	 */
	public function isEnabled($storeView = null)
	{
		return (
			Mage::getStoreConfigFlag(self::FETCHBACK_CONFIG_ENABLED, $storeView) &&
			'' !== $this->getMerchantId($storeViewId)
		);
	}

	/**
	 * Get the FetchBack merchant id from admin configuration.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getMerchantId($storeView = null)
	{
		return Mage::getStoreConfig(self::FETCHBACK_CONFIG_MERCHANT_ID, $storeView);
	}

	/**
	 * Get the ftp username.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getFtpUsername($storeView = null)
	{
		return Mage::getStoreConfig(self::FETCHBACK_CONFIG_FTP_USER, $storeView);
	}

	/**
	 * Get the decrypted ftp password from admin configuration.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getFtpPassword($storeView = null)
	{
		return Mage::helper('core')->decrypt(
			Mage::getStoreConfig(self::FETCHBACK_CONFIG_FTP_PASS, $storeView)
		);
	}

	/**
	 * Get the ftp server hostname.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getFtpHost($storeView = null)
	{
		return Mage::getStoreConfig(self::FETCHBACK_CONFIG_FTP_HOST, $storeView);
	}

	/**
	 * Get the ftp server port.
	 *
	 * @return string
	 */
	public function getFtpPort()
	{
		return Mage::getStoreConfig(self::FETCHBACK_CONFIG_FTP_PORT, $storeView);
	}

	/**
	 * Put a local file on the remote ftp server for Fetchback.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @param string $localPath
	 */
	public function ftpPutFile($localPath, $storeView = null)
	{
		$host = $this->getFtpHost($storeView);
		$user = $this->getFtpUsername($storeView);
		$pass = $this->getFtpPassword($storeView);

		$conn = ftp_connect($host);
		if (!$conn) {
			Mage::throwException("Failed to connect to 'ftp://$host'.");
		}
		$auth = ftp_login($conn, $user, $pass);
		if (!$auth) {
			Mage::throwException("Failed to authenticate to 'ftp://$user@$host'.");
		}
		$pasv = ftp_pasv($conn, true);
		if (!$pasv) {
			Mage::throwException("Failed to switch to passive mode on 'ftp://$host'.");
		}
		Mage::log("Connected to ftp://$user@$host");
		$up = ftp_put($conn, basename($localPath), $localPath, FTP_BINARY);
		if ($up) {
			Mage::log("Uploaded '$localPath' to 'ftp://$host'.");
		} else {
			Mage::throwException("Failed to upload '$localPath' to 'ftp://$host'.");
		}
		ftp_close($conn);
	}

	/**
	 * Join the directories in a canonical, platform-agnostic way.
	 * @param ? Some number of strings
	 * @return The joined path (string)
	 */
	public static function normal_paths()
	{
		$paths = implode(DS, func_get_args());
		// Retain a single leading slash; otherwise remove all leading, trailing
		// and duplicate slashes.
		return ((substr($paths, 0, 1) === DS) ? DS : '') .
			implode(DS, array_filter(explode(DS, $paths)));
	}
}
