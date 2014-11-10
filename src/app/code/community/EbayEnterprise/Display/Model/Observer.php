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
	/**
	 * This observer method is the entry point to generating product feed when
	 * the CRONJOB 'eems_display_products_feed' run.
	 * @return self
	 */
	public function generateProductFeed()
	{
		Mage::getModel('eems_display/products')->export();
		return $this;
	}
}
