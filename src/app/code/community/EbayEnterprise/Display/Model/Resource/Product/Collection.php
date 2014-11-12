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

class EbayEnterprise_Display_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
	/**
	 * Add attribute character limitation filter.
	 * @param string $name
	 * @param int $limit
	 * @return self
	 */
	public function addAttributeCharLimitToFilter($name, $limit=50)
	{
		$sqlSafeName = Mage::helper('eems_display')->makeSqlSafe($name);
		$whereClause = 'CHAR_LENGTH(at_' . $sqlSafeName . '_default.value) <= ?';
		$this->addFieldToFilter($name, array('neq' => null))
			->getSelect()->where($whereClause, $limit);
		return $this;
	}
	/**
	 * Add in stock filter.
	 * @return self
	 */
	public function addInStockToFilter()
	{
		$this->getSelect()->joinLeft(
			array('item_stock' => $this->getTable('cataloginventory/stock_item')),
			'item_stock.product_id=e.entity_id',
			array('product_qty' => 'qty', 'safety_stock' => 'min_qty')
		)
		->columns(array('available_inventory' => '(item_stock.qty - item_stock.min_qty)'))
		->where('item_stock.is_in_stock=?', 1);
		return $this;
	}
}
