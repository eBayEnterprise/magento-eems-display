<?php
class EbayEnterprise_Display_Test_Model_ProductsTest extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * Test that EbayEnterprise_Display_Model_Products::_getProductCollection will return
	 * a Mage_Catalog_Model_Resource_Product_Collection.
	 * @test
	 */
	public function testGetProductCollection()
	{
		$testStoreId = Mage_Core_Model_App::ADMIN_STORE_ID; 
		$productCollection = $this->getResourceModelMockBuilder('catalog/product_collection')
			->disableOriginalConstructor()
			->setMethods(array('addStoreFilter'))
			->getMock();
		$productCollection->expects($this->once())
			->method('addStoreFilter')
			->with($this->identicalTo($testStoreId))
			->will($this->returnSelf());
		$this->replaceByMock('resource_model', 'catalog/product_collection', $productCollection);

		$feedBuilder = Mage::getModel('eems_display/products');

		$this->assertSame($productCollection, EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$feedBuilder, '_getProductCollection', array($testStoreId)
		));
	}
}
