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
 * Ignoring code coverage for this class because it is simply responsible for checking cron setup on the system that is hosting this Magento application.
 * to process whatever is needed to be processed.
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Model_Cron
{
	const CRONTAB_SHELL = 'cron.sh';
	const CRONTAB_COMMAND = 'crontab -l 2> /dev/null | grep cron.sh | head -n 1';
	/**
	 * Check if the current platform is a Linux system.
	 * @return bool true is a Linux system otherwise false
	 */
	protected function _isLinux()
	{
		return (stripos(PHP_OS, 'win') === false);
	}
	/**
	 * Return the result from checking if cron.sh in a system cron tab as an array.
	 * @return array
	 */
	protected function _getCronCheckResult()
	{
		return ($this->_isLinux()) ? array_filter(explode("\n", shell_exec(static::CRONTAB_COMMAND))) : array();
	}
	/**
	 * Check if the Magento cron.sh file is setup as a crontab entry in this system.
	 * @return bool true the cron.sh file was found in the crontab entry otherwise false
	 */
	public function isCronSetup()
	{
		$result = $this->_getCronCheckResult();
		return !empty($result);
	}
}
