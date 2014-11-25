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
	public function getDataRowProvider()
	{
		return array(
			array(
				array(
					'sku' => 'ace000',
					'name' => 'Aviator Sunglasses',
					'short_description' => 'A timeless accessory staple, the unmistakable teardrop lenses of our Aviator sunglasses appeal to everyone from suits to rock stars to citizens of the world.',
					'price' => 295.00,
					'special_price' => 249.99,
					'image' => 'http://localhost/a/resized/image.jpg',
					'url_key' => 'http://localhost/path/to/the/product.html',
					'available_inventory' => 10000
				),
				array(
					'ace000',
					'Aviator Sunglasses',
					'A timeless accessory staple, the unmistakable teardrop lenses of our Aviator sunglasses appeal to everyone from suits to rock stars to citizens of the world.',
					295.00,
					249.99,
					'http://localhost/a/resized/image.jpg',
					'http://localhost/path/to/the/product.html',
					10000
				)
			),
			array(
				array(
					'sku' => 'ace000',
					'name' => 'Aviator Sunglasses',
					'short_description' => 'A timeless accessory staple, the unmistakable teardrop lenses of our Aviator sunglasses appeal to everyone from suits to rock stars to citizens of the world.',
					'price' => 295.00,
					'special_price' => 249.99,
					'image' => null,
					'url_key' => 'http://localhost/path/to/the/product.html',
					'available_inventory' => 10000
				),
				array()
			)
		);
	}

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
			DS . 'ProductsTest' .
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

		$imageName = $this->injectImage($image);
		$resized = EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$feed,
			'_getResizedImage',
			array(
				$product,
				Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
			)
		);
		@unlink($imageName);

		$this->assertStringEndsWith($expected, $resized);
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
	 * This is a procedure test examining that once the method
	 * EbayEnterprise_Display_Model_Products::_buildProductCollection is invoked
	 * passing in store id it will build a `EbayEnterprise_Display_Model_Resource_Product_Collection`
	 * collection object adding the right filters to get products that are only enabled, visible, in-stock,
	 * and product title that are less or equal to the default character limit in the configuration.
	 * @param int $storeId
	 * @param array $attributesToSelect
	 * @param int $titleCharLimit
	 * @param int $pageSize
	 * @dataProvider dataProvider
	 * @loadFixture testBuildProductCollection.yaml
	 */
	public function testBuildProductCollection($storeId, array $attributesToSelect, $titleCharLimit, $pageSize)
	{
		$collection = $this->getResourceModelMock('eems_display/product_collection', array(
			'setStore', 'addAttributeToSelect', 'addFieldToFilter', 'addStoreFilter', 'addAttributeCharLimitToFilter', 'setPageSize', 'addInStockToFilter'
		));
		$collection->expects($this->once())
			->method('setStore')
			->with($this->identicalTo($storeId))
			->will($this->returnSelf());
		$collection->expects($this->once())
			->method('addAttributeToSelect')
			->with($this->identicalTo($attributesToSelect))
			->will($this->returnSelf());
		$collection->expects($this->exactly(2))
			->method('addFieldToFilter')
			->will($this->returnValueMap(array(
				array('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE), $collection),
				array('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED, $collection)
			)));
		$collection->expects($this->once())
			->method('addStoreFilter')
			->with($this->identicalTo(null))
			->will($this->returnSelf());
		$collection->expects($this->once())
			->method('addAttributeCharLimitToFilter')
			->with($this->identicalTo('name'), $titleCharLimit)
			->will($this->returnSelf());
		$collection->expects($this->once())
			->method('addInStockToFilter')
			->will($this->returnSelf());
		$collection->expects($this->once())
			->method('setPageSize')
			->with($this->identicalTo($pageSize))
			->will($this->returnSelf());
		$this->replaceByMock('resource_model', 'eems_display/product_collection', $collection);
		$products = Mage::getModel('eems_display/products');

		$this->assertSame($collection, EcomDev_Utils_Reflection::invokeRestrictedMethod($products, '_buildProductCollection', array($storeId)));
	}

	/**
	 * @param array $attributes array of attributes to initialize the catalog/product model
	 * @param array $expectedReturn
	 * @dataProvider getDataRowProvider
	 */
	public function testGetDataRow(array $attributes, $expectedReturn)
	{
		$product = $this->getModelMock(
			'catalog/product',
			array('getProductUrl'),
			false,
			array($attributes)
		);
		$product->expects($this->any())
			->method('getProductUrl')
			->will($this->returnValue($attributes['url_key']));
	
		$products = $this->getModelMock('eems_display/products', array('_getValidSpecialPrice','_getResizedImage'));
		$products->expects($this->any())
			->method('_getValidSpecialPrice')
			->will($this->returnValue($attributes['special_price']));
		$products->expects($this->any())
			->method('_getResizedImage')
			->will($this->returnValue($attributes['image']));
		$this->replaceByMock('model', 'eems_display/products', $products);
	
		$this->assertSame(
			$expectedReturn,
			EcomDev_Utils_Reflection::invokeRestrictedMethod(
				$products,
				'_getDataRow',
				array($product, 1)
			)
		);
	}
}
