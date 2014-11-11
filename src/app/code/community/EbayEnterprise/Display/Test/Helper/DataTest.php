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
	protected $_imageName;

	/**
	 * Setting update product image
	 * Move the image in the fixture under magento media
	 * directory and save a cache version of it.
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
		$baseImage = 'test-product-img.jpg';
		$this->_imageName = Mage::getBaseDir('media') .
			DIRECTORY_SEPARATOR . 'catalog' .
			DIRECTORY_SEPARATOR . 'product' .
			DIRECTORY_SEPARATOR . static::IMAGE_RELATIVE_PATH;
		$cacheImage = Mage::getBaseDir('media') .
			DIRECTORY_SEPARATOR . 'catalog' .
			DIRECTORY_SEPARATOR . 'product' .
			DIRECTORY_SEPARATOR . 'resize' .
			DIRECTORY_SEPARATOR . $baseImage;
		$fixtureImage = __DIR__ .
			DIRECTORY_SEPARATOR . 'DataTest' .
			DIRECTORY_SEPARATOR . 'fixtures' .
			DIRECTORY_SEPARATOR . $baseImage;

		// @see Varien_Image_Adapter_Gd2::_isMemoryLimitReached
		// There's a bug in Varien_Image_Adapter_Gd2::open that causes
		// an exception to be thrown when the PHP built in method
		// ini_get('memory_limit') return -1, which imply no limit.
		// I'm guessing that the right environment setting are not set when
		// running phpunit with EcomDev.
		if (ini_get('memory_limit') <= 0) {
			ini_set('memory_limit', '512M');
		}

		$productMediaDir = str_replace($baseImage, '', $this->_imageName);
		@mkdir($productMediaDir, 0777, true);

		// if the file already exist remove it.
		@unlink($this->_imageName);

		// Copy the image file in our fixture directory into
		// Magento product media directory.
		@copy($fixtureImage, $this->_imageName);

		$image = new Varien_Image($this->_imageName);
		$image->constrainOnly(true);
		$image->keepAspectRatio(false);
		$image->keepFrame(false);
		$image->keepTransparency(true);
		$image->resize(100, 100);
		$image->save($cacheImage);
	}

	/**
	 * remove the image file
	 * @return void
	 */
	public function tearDown()
	{
		@unlink($this->_imageName);
	}

	/**
	 * @return array
	 */
	public function stripHtmlProvider()
	{
		$expectedReturn = 'This is a really cool, neat product. Here are some of its neat features: First feature Next feature';

		return array(
			array(
				'This is a really cool, neat product. Here are some of its neat features: First feature Next feature',
				$expectedReturn
			),
			array(
				"<body>\n\t<div class=\"product\">\n\t\t<p>This is a really cool, neat product. Here are some of its neat features: <ul><li>First feature </li><li>Next feature</li></p>\n",
				$expectedReturn
			),
		);
	}

	/**
	 * @return array
	 */
	public function isValidImageProvider()
	{
		return array(
			array('no-file.jpg', 0, 0, false)
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
	 * @dataProvider stripHtmlProvider
	 */
	public function testStripHtml($content, $expectedReturn)
	{
		$this->assertSame($expectedReturn, Mage::helper('eems_display')->stripHtml($content));
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

	public function testIsValidImageDoesntCareAboutSize()
	{
		$this->assertTrue(Mage::helper('eems_display')->isValidImage($this->_imageName, 0, 0));
	}
}
