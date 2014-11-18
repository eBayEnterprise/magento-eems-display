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
	const IMAGE_RELATIVE_PATH = 'p/r/test-product-img.jpg';
	/**
	 * @var string, hold the full path to the product image.
	 */
	protected $_fixtureDir;

	/**
	 * Setting update product image
	 * Move the image in the fixture under magento media
	 * directory and save a cache version of it.
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
		$this->_fixtureDir = __DIR__ .
			DIRECTORY_SEPARATOR . 'DataTest' .
			DIRECTORY_SEPARATOR . 'fixtures';
	}

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
				'This is a really cool®®, neat product. Here are some of its neat features: First (première) feature Next feature日本'
			),
			array(
				'Weiß, Goldmann, Göbel, Weiss, Göthe, Goethe und Götz',
				'Weiß, Goldmann, Göbel, Weiss, Göthe, Goethe und Götz'
				//'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz'
			)
		);
	}

	/**
	 * @return array
	 */
	public function isValidImageProvider()
	{
		$fixtureDir = __DIR__ .
			DIRECTORY_SEPARATOR . 'DataTest' .
			DIRECTORY_SEPARATOR . 'fixtures';
	
		$mageDir = Mage::getBaseDir();
		$relDir = str_replace($mageDir, '', $fixtureDir);
		$relDir = ltrim($relDir, '/');
	
		return array(
			array($fixtureDir . DIRECTORY_SEPARATOR . 'test-product-img.jpg', 0, 0, true),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'test-product-img.jpg', 150, 150, false),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'test-product-img-150-150.jpg', 150, 150, true),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'not-an-image.jpg', 0, 0, false),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'not-an-image.jpg', 150, 150, false),
			array('no-file.jpg', 0, 0, false),
			array('http://example.com/'. $relDir . DIRECTORY_SEPARATOR . 'test-product-img-150-150.jpg', 150, 150, true),
			array('http://example.com/'. $relDir . DIRECTORY_SEPARATOR . 'no-file.jpg', 150, 150, false),
			array('http://example.com/'. $relDir . DIRECTORY_SEPARATOR, 150, 150, false),
			array('http://some.other.host/'. $relDir . DIRECTORY_SEPARATOR . 'test-product-img-150-150.jpg', 150, 150, true),
		);
	}

	/**
	 * @return array
	 */
	public function getLocalPathFromUrlProvider()
	{
		$mageDir = Mage::getBaseDir();

		return array(
			array('http://localhost/this/is/a/file.jpg', $mageDir.'/this/is/a/file.jpg'),
			array('/localhost/this/is/a/file.jpg', '/localhost/this/is/a/file.jpg')
		);
	}

	/**
	 * @return array
	 */
	public function isLocalUrlProvider()
	{
		return array(
			array('http://localhost/index.php', true),
			array('http://some.other.host/index.php', false)
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
	 * @param string $filename file name or url
	 * @param int $width
	 * @param int $height
	 * @param bool $expectedReturn
	 * @dataProvider isValidImageProvider
	 */
	public function testIsValidImage($filename, $width, $height, $expectedReturn)
	{
		$this->assertEquals($expectedReturn, Mage::helper('eems_display')->isValidImage($filename, $width, $height));
	}

	/**
	 * @param string $url
	 * @param string $expectedReturn
	 * @dataProvider getLocalPathFromUrlProvider
	 */
	public function testGetLocalPathFromUrl($url, $expectedReturn)
	{
		$this->assertSame($expectedReturn, Mage::helper('eems_display')->getLocalPathFromUrl($url));
	}

	/**
	 * @param string $url
	 * @param $expectedResult
	 * @dataProvider isLocalUrlProvider
	 * @loadFixture testIsLocalUrl.yaml
	 */
	public function testIsLocalUrl($url, $expectedResult)
	{
		$this->assertSame($expectedResult, Mage::helper('eems_display')->isLocalUrl($url));
	}
}
