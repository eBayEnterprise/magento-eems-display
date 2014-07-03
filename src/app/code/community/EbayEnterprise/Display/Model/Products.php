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

class EbayEnterprise_Display_Model_Products extends Mage_Core_Model_Abstract
{
	const CSV_FIELD_DELIMITER = ',';
	const CSV_FIELD_ENCLOSURE = '"';
	/**
	 * Export product feeds. Get every store from every store group for every website.
	 */
	public function export()
	{
		// Email won't send if it's previously been sent. And I'm doing
		// The installation notification here because it seemed as good a place
		// as any, and seemed likely that we'd have something configured by now.
		Mage::getModel('eems_display/email')->sendInstalledNotification();
		foreach (Mage::app()->getWebsites() as $website) {
			foreach ($website->getGroups() as $storeGroup) {
				$this->_processStores($storeGroup->getStores());
			}
		}
	}
	/**
	 * Puts an array of data into a file pointed to by fileHandle
	 * @param $outputFileName (full path)
	 * @param $dataRows
	 * @return self
	 */
	protected function _createCsvFile($outputFileName, $dataRows)
	{
		$fh = fopen($outputFileName, 'w');
		if ($fh === false) {
			Mage::log('Cannot open file for writing: ' . $outputFileName);
		} else {
			foreach ($dataRows as $row) {
				fputcsv( $fh, $row, self::CSV_FIELD_DELIMITER, self::CSV_FIELD_ENCLOSURE);
			}
			fclose($fh);
		}
		return $this;
	}
	/**
	 * Processes output files for one Store Group. Each store that has a
	 * non-empty SiteId and is enabled gets a feed output. File name
	 * is StoreId.csv and it's placed in the configured feed file path.
	 */
	protected function _processStores($stores)
	{
		$helper   = Mage::helper('eems_display/config');
		$dirName  = $helper->getFeedFilePath();
		foreach ($stores as $store) {
			$storeId = $store->getId();
			$siteId  = $helper->getSiteId($storeId);
			if (empty($siteId) || !$helper->getIsEnabled($storeId)) {
				continue;
			}
			$outputFileName = $dirName . DS . $storeId . '.csv';
			$rows = array_merge(
				array($this->_getProductDataHeader()),
				$this->_getProductData($storeId)
			);
			$this->_createCsvFile($outputFileName, $rows);
		}
		return $this;
	}
	/**
	 * Get the product collection for this store.
	 * @return object (collection of Mage_Sales_Model_Order)
	 */
	protected function _getProductCollection($storeId=null)
	{
		return Mage::getResourceModel('catalog/product_collection')
			->setStore($storeId)
			->addAttributeToSelect(array('*'))
			->addStoreFilter($storeId);
	}
	/**
	 * Return an array of header row values.
	 * @return array
	 */
	protected function _getProductDataHeader()
	{
		return array('Id', 'Name', 'Description', 'Price', 'Image URL', 'Page URL');
	}
	/**
	 * Gets the product image URL, ensuring that it gets resized
	 * @return image url, or blank if we can't get if figured out
	 */
	protected function _getResizedImage($product, $storeId)
	{
		$helper = Mage::helper('eems_display/config');
		try {
			// Image implementation doesn't save the resize filed unless it's coerced into
			// a string. Its (php) magic '__toString()' method is what actually resizes and saves
			$imageUrl = (string) Mage::helper('catalog/image')
				->init($product, 'image')
				->keepAspectRatio(
					$helper->getFeedImageKeepAspectRatio($storeId)
				)
				->resize(
					$helper->getFeedImageWidth($storeId),
					$helper->getFeedImageHeight($storeId)
				);
		} catch (Exception $e) {
			$imageUrl = '';
			Mage::log('Error sizing Image URL for ' . $product->getSku(), $e->getMessage());
		}
		return $imageUrl;
	}
	/**
	 * Compile the product data for Fetchback into
	 * an array that can be put into a CSV.
	 * @return array
	 */
	protected function _getProductData($storeId)
	{
		$data     = array();
		$products = $this->_getProductCollection($storeId);
		$helper = Mage::helper('eems_display');
		foreach($products as $product) {
			$data[] = array(
				$product->getSku(),
				$helper->cleanString($product->getName()),
				$helper->cleanString($product->getShortDescription()),
				$product->getPrice(),
				$this->_getResizedImage($product, $storeId),
				$product->getProductUrl(),
			);
		}
		return $data;
	}
}
