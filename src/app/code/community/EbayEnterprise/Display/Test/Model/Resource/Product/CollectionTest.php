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

class EbayEnterprise_Display_Test_Model_Resource_Product_CollectionTest
	extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * This is a procedure test testing that the method
	 * EbayEnterprise_Display_Model_Resource_Product_Collection::addAttributeCharLimitToFilter
	 * passing in an attribute name and character limit and expect a `EbayEnterprise_Display_Model_Resource_Product_Collection`
	 * instance with attribute to filter and the character limit SQL statement in the where clause.
	 * @param int $attribute
	 * @param int $titleCharLimit
	 * @param string $whereStatement
	 * @dataProvider dataProvider
	 */
	public function testAddAttributeCharLimitToFilter($attribute, $titleCharLimit, $whereStatement)
	{
		$select = $this->getMockBuilder('Varien_Db_Select')
			// preventing 'Varien_Db_Select::__construct() must be an instance of Zend_Db_Adapter_Abstract' from
			// causing Missing argument exception from being thrown.
			->disableOriginalConstructor()
			->setMethods(array('where'))
			->getMock();
		$select->expects($this->once())
			->method('where')
			->with($this->identicalTo($whereStatement), $this->identicalTo($titleCharLimit))
			->will($this->returnSelf());

		$collection = $this->getResourceModelMockBuilder('eems_display/product_collection')
			// preventing the method 'Mage_Catalog_Model_Resource_Product_Collection::_initSelect()' from running
			// and causing PHP Fatal error like 'Call to a member function from() on a non-object'.
			->disableOriginalConstructor()
			->setMethods(array('addFieldToFilter', 'getSelect'))
			->getMock();
		$collection->expects($this->once())
			->method('addFieldToFilter')
			->with($this->identicalTo($attribute), $this->identicalTo(array('neq' => null)))
			->will($this->returnSelf());
		$collection->expects($this->once())
			->method('getSelect')
			->will($this->returnValue($select));

		$this->assertSame($collection, $collection->addAttributeCharLimitToFilter($attribute, $titleCharLimit));
	}
	/**
	 * This is a procedure test testing that the method
	 * EbayEnterprise_Display_Model_Resource_Product_Collection::addInStockToFilter
	 * when invoke will instantiate a `EbayEnterprise_Display_Model_Resource_Product_Collection`
	 * object with join statement to join product collection with stock_item collection and where clause
	 * statements to filter out out of stock items.
	 * @param int $tableAlias
	 * @param int $tableName
	 * @param string $condition
	 * @param array $fields
	 * @param array $columns
	 * @param string $whereStatement
	 * @dataProvider dataProvider
	 */
	public function testAddInStockToFilter($tableAlias, $tableName, $condition, array $fields, array $columns, $whereStatement)
	{
		$select = $this->getMockBuilder('Varien_Db_Select')
			// preventing 'Varien_Db_Select::__construct() must be an instance of Zend_Db_Adapter_Abstract' from
			// causing Missing argument exception from being thrown.
			->disableOriginalConstructor()
			->setMethods(array('joinLeft', 'columns', 'where'))
			->getMock();
		$select->expects($this->once())
			->method('joinLeft')
			->with($this->identicalTo(array('item_stock' => $tableName)), $this->identicalTo($condition), $this->identicalTo($fields))
			->will($this->returnSelf());
		$select->expects($this->once())
			->method('columns')
			->with($this->identicalTo($columns))
			->will($this->returnSelf());
		$select->expects($this->once())
			->method('where')
			->with($this->identicalTo($whereStatement), $this->identicalTo(1))
			->will($this->returnSelf());

		$collection = $this->getResourceModelMockBuilder('eems_display/product_collection')
			// preventing the method 'Mage_Catalog_Model_Resource_Product_Collection::_initSelect()' from running
			// and causing PHP Fatal error like 'Call to a member function from() on a non-object'.
			->disableOriginalConstructor()
			->setMethods(array('getTable', 'getSelect'))
			->getMock();
		$collection->expects($this->once())
			->method('getTable')
			->with($this->identicalTo($tableAlias))
			->will($this->returnValue($tableName));
		$collection->expects($this->once())
			->method('getSelect')
			->will($this->returnValue($select));

		$this->assertSame($collection, $collection->addInStockToFilter());
	}
}
