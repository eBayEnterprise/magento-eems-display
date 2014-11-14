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

class EbayEnterprise_Display_Model_Products
{
	const CSV_FIELD_DELIMITER = ',';
	const CSV_FIELD_ENCLOSURE = '"';
	const CSV_FILE_EXTENSION = '.csv';

	/** @var EbayEnterprise_Display_Helper_Config $_config */
	protected $_config;
	/** @var EbayEnterprise_MageLog_Helper_Data $_helper */
	protected $_helper;
	/** @var EbayEnterprise_Display_Model_File_Adapter $_adapter */
	protected $_adapter;

	public function __construct()
	{
		$this->_config = Mage::helper('eems_display/config');
		$this->_helper = Mage::helper('eems_display');
		$this->_adapter = Mage::getModel('eems_display/file_adapter', array(
			'csv_field_delimiter' => static::CSV_FIELD_DELIMITER,
			'csv_field_enclosure' => static::CSV_FIELD_ENCLOSURE
		));
	}
	/**
	 * Export product feeds. Get every store from every store group for every website.
	 */
	public function export()
	{
		Mage::dispatchEvent('eems_display_generate_product_feed_before', array());
		foreach (Mage::app()->getWebsites() as $website) {
			foreach ($website->getGroups() as $storeGroup) {
				$this->_processStores($storeGroup->getStores());
			}
		}
		Mage::dispatchEvent('eems_display_generate_product_feed_after', array());
	}

	/**
	 * The CSV file name per store id
	 * @param  int $storeId
	 * @return string
	 */
	protected function _getCsvFile($storeId)
	{
		return $this->_config->getFeedFilePath() . DS . $storeId . static::CSV_FILE_EXTENSION;
	}
	/**
	 * Processes output files for one Store Group. Each store that has a
	 * non-empty SiteId and is enabled gets a feed output. File name
	 * is StoreId.csv and it's placed in the configured feed file path.
	 * @param $stores array of Mage_Core_Model_Store(s) to process
	 * @return self
	 */
	protected function _processStores(array $stores)
	{
		foreach ($stores as $store) {
			$storeId = $store->getId();
			$siteId  = $this->_config->getSiteId($storeId);
			if (empty($siteId) || !$this->_config->getIsEnabled($storeId)) {
				// If this store doesn't have a site id, or isn't enabled for Display, skip it
				continue;
			}
			try{
				$this->_adapter->openCsvFile($this->_getCsvFile($storeId));
			} catch (EbayEnterprise_Display_Model_File_Exception $e) {
				// If we can't open the output file, complain to log and skip this store
				Mage::log($e->getMessage());
				continue;
			}
			$this->_adapter->addNewCsvRow($this->_config->getFeedHeaderColumns());
			$this->_writeDataRows($storeId);
			$this->_adapter->closeCsvFile();
		}
		return $this;
	}
	/**
	 * Gets the product image URL, ensuring that it gets resized
	 * @param $product One Mage_Catalog_Model_Product from a Collection
	 * @param $storeId int which store Id this is for
	 * @return image url, or blank if we can't get it figured out
	 */
	protected function _getResizedImage(Mage_Catalog_Model_Product $product, $storeId)
	{
		try {
			// Image implementation doesn't save the resize filed unless it's coerced into
			// a string. Its (php) magic '__toString()' method is what actually resizes and saves
			$imageUrl = (string) Mage::helper('catalog/image')
				->init($product, 'image')
				->keepAspectRatio(
					$this->_config->getFeedImageKeepAspectRatio($storeId)
				)
				->resize(
					$this->_config->getFeedImageWidth($storeId),
					$this->_config->getFeedImageHeight($storeId)
				);
		} catch (Exception $e) {
			$imageUrl = '';
			Mage::log("Error sizing Image URL for {$product->getSku()}: {$e->getMessage()}");
		}

        	// $imageUrl should be valid or an empty string but some customers are reporting invalid URLs in their feed
        	// so we add one last check to make sure we have a valid URL or return an empty string if we don't
        	if (!$this->_helper->isValidImage(
            		$imageUrl,
            		$this->_config->getFeedImageWidth($storeId),
            		$this->_config->getFeedImageHeight($storeId)
        		)
		) {
            		$imageUrl = '';
        	}	

        return $imageUrl;
	}

	/**
	 * Checks if the special price has expired
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param int | null $store
	 * @return string | null special price or null if the special price has expired
	 */
	protected function _getValidSpecialPrice(Mage_Catalog_Model_Product $product, $store=null)
	{
		$price = null;
		$fromDate = $product->getSpecialFromDate();
		$toDate = $product->getSpecialToDate();

		if (Mage::app()->getLocale()->isStoreDateInInterval($store, $fromDate, $toDate)) {
			$price = $product->getSpecialPrice();
		}

		return $price;
	}
	/**
	 * Build product collection
	 * @param  int $storeId Store id
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 *
	 * The collection is formed thus:
	 * 1. Since we are working with multiple stores, we use Mage:getResourceModel('catalog/product_collection') instead of
	 * 		Mage::getModel('catalog/product')->getCollection() because the latter will load some store context we
	 * 		prefer to control explicitly.
	 * 2. setStore() is a method that will set up store context we do need. It does not set filtering.
	 * 3. addAttributeToSelect() to get only the fields we need. Each product's _data will contain only these fields.
	 * 4. addFieldToFilter() basically our 'where' clauses
	 * 5. addStoreFilter() called without arguments defaults to the store most recently setStore()'d. Admin store is explicitly
	 * 		ignored by addStoreFilter().
	 * 6. setPageSize() in order to limit collection's use of memory. This is the number of products we'll load at once.
	 */
	protected function _buildProductCollection($storeId)
	{
		$collection = Mage::getResourceModel('eems_display/product_collection')
			->setStore($storeId)
			->addAttributeToSelect(array('sku', 'name', 'short_description', 'price', 'special_price', 'special_from_date', 'special_to_date', 'url_key', 'image'))
			->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
			->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
			->addStoreFilter()
			->addAttributeCharLimitToFilter('name', $this->_config->getProductTitleCharLimit())
			->addInStockToFilter() // Filter out any product with an out of stock inventory availability status
			->setPageSize($this->_config->getProductFeedPageSize());
		return $collection;
	}
	/**
	 * Compile the product data for display into
	 * an array that can be put into a CSV.
	 * @param  int $storeId Store id
	 * @return self
	 */
	protected function _writeDataRows($storeId)
	{
		$products = $this->_buildProductCollection($storeId);
		$lastPage = $products->getLastPageNumber();
		for($page = 1; $page <= $lastPage; $page++) {
			$products->setCurPage($page);
			foreach ($products as $product) {
                $dataRow = $this->_getDataRow($product, $storeId);
                if ($dataRow) {
                    $this->_adapter->addNewCsvRow($this->_getDataRow($product, $storeId));
                }
			}
			$products->clear();
		}

		return $this;
	}
	/**
	 * Return an array of CSV row data.
	 * @param  Mage_Catalog_Model_Product $product
	 * @param  int $storeId
	 * @return array | null returns null if we don't have a va;id image URL
	 */
	protected function _getDataRow(Mage_Catalog_Model_Product $product, $storeId)
	{
        	// if we don't have a valid image URL return null so we can skip this product in the feed
        	$resized = $this->_getResizedImage($product, $storeId);
        	if (empty($resized)) {
            		return null;
        	}

		return array(
			$product->getSku(),
			$this->_helper->cleanString($product->getName()),
			$this->_helper->cleanStringForFeed($product->getShortDescription()),
			$product->getPrice(),
			$this->_getValidSpecialPrice($product, $storeId),
			$this->_getResizedImage($product, $storeId),
			$product->getProductUrl(),
			$product->getAvailableInventory(),
		);
	}
}
