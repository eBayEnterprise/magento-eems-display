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
	 * Move the image in the fixture under magento media
	 * directory and save a cache version of it.
	 *
	 * @param string $image
	 * @return string
	 */
	protected function injectImage($image)
	{
		if (empty($image)) {
			return '';
		}

		$baseImage = basename($image);
		$imageName = Mage::getBaseDir('media') .
			DS . 'catalog' .
			DS . 'product' .
			DS . $image;
		$cacheImage = Mage::getBaseDir('media') .
			DS . 'catalog' .
			DS . 'product' .
			DS . 'resize' .
			DS . $baseImage;
		$fixtureImage = __DIR__ .
			DS . 'DataTest' .
			DS . 'fixtures' .
			DS . $baseImage;

		// @see Varien_Image_Adapter_Gd2::_isMemoryLimitReached
		// There's a bug in Varien_Image_Adapter_Gd2::open that causes
		// an exception to be thrown when the PHP built in method
		// ini_get('memory_limit') return -1, which imply no limit.
		// I'm guessing that the right environment setting are not set when
		// running phpunit with EcomDev.
		if (ini_get('memory_limit') <= 0) {
			ini_set('memory_limit', '512M');
		}

		$productMediaDir = str_replace($baseImage, '', $imageName);
		@mkdir($productMediaDir, 0777, true);

		// if the file already exist remove it.
		@unlink($imageName);

		// Copy the image file in our fixture directory into
		// Magento product media directory.
		@copy($fixtureImage, $imageName);

		$image = new Varien_Image($imageName);
		$image->constrainOnly(true);
		$image->keepAspectRatio(false);
		$image->keepFrame(false);
		$image->keepTransparency(true);
		$image->resize(100, 100);
		$image->save($cacheImage);

		return $imageName;
	}

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
	public function isValidImageLocalFileProvider()
	{
		$fixtureDir = __DIR__ .
			DIRECTORY_SEPARATOR . 'DataTest' .
			DIRECTORY_SEPARATOR . 'fixtures';

		return array(
			array($fixtureDir . DIRECTORY_SEPARATOR . 'test-product-img.jpg', 0, 0, true),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'test-product-img.jpg', 150, 150, false),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'test-product-img-150-150.jpg', 150, 150, true),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'not-an-image.jpg', 0, 0, false),
			array($fixtureDir . DIRECTORY_SEPARATOR . 'not-an-image.jpg', 150, 150, false),
			array('no-file.jpg', 0, 0, false)
		);
	}

	/**
	 * @return array
	 */
	public function isValidImageLocalUrlProvider()
	{
		$fixtureDir = __DIR__ .
			DIRECTORY_SEPARATOR . 'DataTest' .
			DIRECTORY_SEPARATOR . 'fixtures';

		$mageDir = Mage::getBaseDir();
		$relDir = str_replace($mageDir, '', $fixtureDir);
		$relDir = ltrim($relDir, '/');

		return array(
			array($relDir . DIRECTORY_SEPARATOR . 'test-product-img-150-150.jpg', 150, 150, true),
			array($relDir . DIRECTORY_SEPARATOR . 'no-file.jpg', 150, 150, false),
			array($relDir . DIRECTORY_SEPARATOR, 150, 150, false)
		);
	}

	/**
	 * @return array
	 */
	public function imageSizeForFeedProvider()
	{
		return array(
			array(1, 'p/r/test-product-img.jpg', array(600, 315)),
			array(1, 'p/r/test-product-large-img.jpg', array(600, 315)),
			array(1, 'p/r/test-product-large-wide-img.jpg', array(600, 315)),
			array(1, 'p/r/test-product-img-150-150.jpg', array(150, 150))
		);
	}

	/**
	 * @return array
	 */
	public function isLocalUrlProvider()
	{
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		return array(
			array("$baseUrl/index.php", true),
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
	 * @dataProvider isValidImageLocalFileProvider
	 */
	public function testIsValidImageWithLocalFiles($filename, $width, $height, $expectedReturn)
	{
		$this->assertEquals($expectedReturn, Mage::helper('eems_display')->isValidImage($filename, $width, $height));
	}

	/**
	 * @param string $filename file name or url
	 * @param int $width
	 * @param int $height
	 * @param bool $expectedReturn
	 * @dataProvider isValidImageLocalUrlProvider
	 */
	public function testIsValidImageWithLocalUrl($filename, $width, $height, $expectedReturn)
	{
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$this->assertEquals(
			$expectedReturn,
			Mage::helper('eems_display')->isValidImage("{$baseUrl}{$filename}", $width, $height)
		);
	}

	/**
	 *
	 */
	public function testIsValidImageWithRemoteFile()
	{
		$this->assertTrue(
			Mage::helper('eems_display')
				->isValidImage(
					'http://some.other.host/test-product-img-150-150.jpg',
					150,
					150
				)
		);
	}

	/**
	 * @param int $entityId
	 * @param string $image
	 * @param array $expectedReturn
	 * @dataProvider imageSizeForFeedProvider
	 */
	public function testImageSizeForFeed($entityId, $image, array $expectedReturn)
	{
		$product = Mage::getModel('catalog/product', array('entity_id' => $entityId, 'image' => $image));
		$imageName = $this->injectImage($image);
		$size = Mage::helper('eems_display')->imageSizeForFeed($product, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
		@unlink($imageName);

		$this->assertSame($expectedReturn, array($size['width'], $size['height']));
	}

	/**
	 *
	 */
	public function testGetLocalPathFromUrlWithLocalFile()
	{
		$this->assertSame(
			'/localhost/this/is/a/file.jpg',
			Mage::helper('eems_display')->getLocalPathFromUrl('/localhost/this/is/a/file.jpg')
		);
	}

	/**
	 *
	 */
	public function testGetLocalPathFromUrlWithUrl()
	{
		$mageDir = Mage::getBaseDir();
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

		$this->assertSame(
			"$mageDir/this/is/a/file.jpg",
			Mage::helper('eems_display')->getLocalPathFromUrl("{$baseUrl}this/is/a/file.jpg")
		);
	}

	/**
	 *
	 */
	public function testIsLocalUrlWithLocalUrl()
	{
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$this->assertTrue(Mage::helper('eems_display')->isLocalUrl("$baseUrl/index.php"));
	}

	/**
	 *
	 */
	public function testIsLocalUrlWithNonLocalUrl()
	{
		$this->assertFalse(Mage::helper('eems_display')->isLocalUrl('http://some.other.host/index.php'));
	}
}
