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

class EbayEnterprise_Display_Test_Model_ProductsTest
	extends EcomDev_PHPUnit_Test_Case
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
			DIRECTORY_SEPARATOR . 'ProductsTest' .
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
	public 	function getValidSpecialPriceProvider()
	{
		// using explicit date string so we don't have to worry about
		// locale specific formatting
		// October 31, 2014 vs 10/31/2014 vs 31/10/2014

		$today = date('F j, Y');
		return array(
			array(null, null, null, 1, null),
			array(9.9900, 'January 1, 2014', 'January 31, 2014', 1, null),
			array(9.9900, 'January 1, 2014', 'January 1, 2038', 1, 9.9900),
			array(9.9900, 'January 1, 2037', 'January 1, 2038', 1, null),
			array(9.9900, 'January 1, 2038', 'January 1, 2014', 1, null),
			array(9.9900, null, 'January 1, 2038', 1, 9.9900),
			array(9.9900, null, 'January 1, 2014', 1, null),
			array(9.9900, 'January 1, 2014', null, 1, 9.9900),
			array(9.9900, 'January 1, 2037', null, 1, null),
			array(9.9900, null, null, 1, 9.9900),
			array(9.9900, $today, null, 1, 9.9900),
			array(9.9900, $today, $today, 1, 9.9900)
		);
	}

	/**
	 * @return array
	 */
	public function stripHtmlProvider()
	{
		$expectedReturn = array(
			'sku123',
			'Product Name',
			'This is a really cool product. Here are some of its neat features: First feature Next feature',
			'9.99',
			'7.99',
			'http://some.site.com/path/to/image.jpg',
			'http://some.site.com/path/to/product.html'
		);

		return array(
			array(
				'sku123',
				'Product Name',
				'This is a really cool product. Here are some of its neat features: First feature Next feature',
				'9.99',
				'7.99',
				'http://some.site.com/path/to/image.jpg',
				'http://some.site.com/path/to/product.html',
				$expectedReturn
			),
			array(
				'<body><div class="product"><p>sku123</p>',
				'<h2>Product Name</h2>',
				'<p>This is a really cool product. Here are some of its neat features: <ul><li>First feature </li><li>Next feature</li></p>',
				'<p>9.99</p>',
				'<p>7.99</p>',
				'<a href="http://some.site.com/path/to/image.jpg">http://some.site.com/path/to/image.jpg</a>',
				'<a href="http://some.site.com/path/to/product.html">http://some.site.com/path/to/product.html</a></div></body></div></body>',
				$expectedReturn
			),
			array(
				'<body><div class="product"><p>sku123</p>',
				'<h2>Product Name',
				'<p>This is a really cool product. Here are some of its neat features: <ul><li>First feature </li><li>Next feature</li></p>',
				'<p>9.99</p>',
				'<p>7.99</p>',
				'<a href="http://some.site.com/path/to/image.jpg">http://some.site.com/path/to/image.jpg</a>',
				'<a href="http://some.site.com/path/to/product.html">http://some.site.com/path/to/product.html</a></div></body></div></body>',
				$expectedReturn
			)
		);
	}

	/**
	 * Test that the method EbayEnterprise_Display_Model_Products::_getResizedImage
	 * when passed in a product that has a valid image data loaded to it will
	 * return the proper location of the image otherwise it will return a placeholder image.
	 * @group IntegrationTest
	 * @dataProvider dataProvider
	 */
	public function testGetResizedImage($entityId, $image, $expected)
	{
		$product = Mage::getModel('catalog/product', array('entity_id' => $entityId, 'image' => $image));
		$feed = Mage::getModel('eems_display/products');
		$this->assertStringEndsWith($expected, EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$feed, '_getResizedImage', array($product, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
		));
	}

	/**
	 * @param float | null $specialPrice
	 * @param string | null $fromDate
	 * @param string | null $toDate
	 * @param float | null $expectedReturn
	 * @dataProvider getValidSpecialPriceProvider
	 */
	public function testGetValidSpecialPrice($specialPrice, $fromDate, $toDate, $store, $expectedReturn)
	{
		$product = Mage::getModel('catalog/product',
			array(
				'special_price' => $specialPrice,
				'special_from_date' => $fromDate,
				'special_to_date' => $toDate
			)
		);

		$products = Mage::getModel('eems_display/products');
		$price = EcomDev_Utils_Reflection::invokeRestrictedMethod($products, '_getValidSpecialPrice', array($product, $store));
		$this->assertEquals($expectedReturn, $price);
	}

	/**
	 * @param string $sku
	 * @param string $name
	 * @param string $description
	 * @param string $price
	 * @param string $specialPrice
	 * @param string $image
	 * @param string $url
	 * @param array $expectedReturn
	 * @dataProvider stripHtmlProvider
	 */
	public function testStripHtml($sku, $name, $description, $price, $specialPrice, $image, $url, $expectedReturn)
	{
		$dataRow = array(
			$sku,
			$name,
			$description,
			$price,
			$specialPrice,
			$image,
			$url
		);

		$products = Mage::getModel('eems_display/products');
		$row = EcomDev_Utils_Reflection::invokeRestrictedMethod($products, '_stripHtml', array($dataRow));
		$this->assertSame($expectedReturn, $row);
	}
}
