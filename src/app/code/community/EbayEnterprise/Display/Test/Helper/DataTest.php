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

class EbayEnterprise_Display_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * @return array
	 */
	public function cleanStringForFeedProvider()
	{
		$expectedReturn = 'This is a really cool, neat product. Here are some of its neat features: First (premiere) feature Next feature';

		return array(
			array(
				'This is a really cool, neat product. Here are some of its neat features: First (premiere) feature Next feature',
				$expectedReturn
			),
			array(
				"<body>\n\t<div class=\"product\">\n\t\t<p>This is a really cool&reg;®, neat product. Here are some of its neat features: <ul><li>First (première) feature </li><li>Next feature</li><li>日本</li></p>\n",
				$expectedReturn
			),
			array(
				'Weiß, Goldmann, Göbel, Weiss, Göthe, Goethe und Götz',
				'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz'
			)
		);
	}

	/**
	 */
	public function testGetProductFeedUrl()
	{
		$testStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
		$testUrl = Mage::helper('eems_display')->getProductFeedUrl($testStoreId);
		$this->assertStringStartsWith('http', $testUrl);
		$this->assertStringEndsWith(
			EbayEnterprise_Display_Helper_Data::EEMS_DISPLAY_PRODUCT_FEED_ROUTE . $testStoreId,
			$testUrl
		);
	}
	/**
	 * Test that the method EbayEnterprise_Display_Helper_Data::cleanString
	 * will normalize a string with multiple carriage returns and/ or line feeds
	 * into a single line.
	 */
	public function testCleanString()
	{
		$content = '

This is a string with
multiple lines
break to prove the
the clean helper method will normalize the string
accordingly


';
		$expect = 'This is a string with multiple lines break to prove the the clean helper method will normalize the string accordingly';

		$this->assertSame($expect, Mage::helper('eems_display')->cleanString($content));
	}

	/**
	 * @param string $content
	 * @param array $expectedReturn
	 * @dataProvider cleanStringForFeedProvider
	 */
	public function testCleanStringForFeed($content, $expectedReturn)
	{
		$this->assertSame($expectedReturn, Mage::helper('eems_display')->cleanStringForFeed($content));
	}

	/**
	 * Test that EbayEnterprise_display_Helper_Data::stripNonAsciiCharacters
	 * properly removes any character above code 0x7e
	 */
	public function testStripNonAsciiChars()
	{
		$this->assertSame(
			"This string contains some non-ascii characters here, , and here, ",
			Mage::helper('eems_display')->stripNonAsciiChars("This string contains some non-ascii characters here, ®, and here, 日本")
		);
	}
}
