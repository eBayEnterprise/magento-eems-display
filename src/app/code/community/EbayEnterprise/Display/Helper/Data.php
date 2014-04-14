<?php
class EbayEnterprise_Display_Helper_Data extends Mage_Core_Helper_Abstract
{
	const DISPLAY_CONFIG_ENABLED  = 'ebayenterprise/display/enabled';
	const DISPLAY_CONFIG_SITE_ID  = 'ebayenterprise/display/merchant_id';
	const DISPLAY_CONFIG_FTP_HOST = 'ebayenterprise/display/ftp_host';
	const DISPLAY_CONFIG_FTP_USER = 'ebayenterprise/display/ftp_username';
	const DISPLAY_CONFIG_FTP_PASS = 'ebayenterprise/display/ftp_password';
	const DISPLAY_CONFIG_FTP_PORT = 'ebayenterprise/display/ftp_port';
	// @TODO We need the Product Feed URL - somehow.
	// @TODO The FTP stuff may not be necessary if we are publishing a URL

	/**
	 * Get whether or not this extension is enabled.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return boolean
	 */
	public function isEnabled($storeView = null)
	{
		// @TODO Suspect code, getSiteId was passing '$storeViewId', which was an undefined variable
		return (
			Mage::getStoreConfigFlag(self::DISPLAY_CONFIG_ENABLED, $storeView) &&
			'' !== $this->getSiteId($storeView)
		);
	}

	/**
	 * Get the FetchBack merchant id from admin configuration.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getSiteId($storeView = null)
	{
		return Mage::getStoreConfig(self::DISPLAY_CONFIG_SITE_ID, $storeView);
	}

	/**
	 * Get the ftp username.
	 *
	 * @param Mage_Core_Model_Store $storeView
	 * @return string
	 */
	public function getFtpUsername($storeView = null)
	{
		return Mage::getStoreConfig(self::DISPLAY_CONFIG_FTP_USER, $storeView);
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
			Mage::getStoreConfig(self::DISPLAY_CONFIG_FTP_PASS, $storeView)
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
		return Mage::getStoreConfig(self::DISPLAY_CONFIG_FTP_HOST, $storeView);
	}

	/**
	 * Get the ftp server port.
	 *
	 * @return string
	 */
	public function getFtpPort()
	{
		// @TODO  - Suspicious code, we hadn't passed in $storeView??
		return Mage::getStoreConfig(self::DISPLAY_CONFIG_FTP_PORT, $storeView);
	}

	/**
	 * @TODO Do we even ftp at all? If not BOOM get rid of all this.
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
