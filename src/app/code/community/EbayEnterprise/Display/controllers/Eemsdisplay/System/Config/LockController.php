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

class EbayEnterprise_Display_Eemsdisplay_System_Config_LockController
	extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Remove product feed lock file.
	 * @return self
	 */
	public function removelockfileAction()
	{
		$this->getResponse()->setHeader('Content-Type', 'text/json')
			->setBody(json_encode(Mage::getModel('eems_display/file_lock')->processLockFileRemoval()));
		return $this;
	}
}
