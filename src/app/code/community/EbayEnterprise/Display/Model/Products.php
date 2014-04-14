<?php
class EbayEnterprise_Display_Model_Products extends Mage_Core_Model_Abstract
{
	/**
	 * @TODO Cleanups for the 'final' implementation of export.
	 * Export product feeds, somehow, by store (I guess)/ Site Id? I guess?
	 */
	public function export()
	{
		/**
		 * @TODO Tighten this up and match up with a custom backend model
		 * that will let us display a link to the feed.
		 */
		$exportDirName  = Mage::getBaseDir('var');
		foreach (Mage::app()->getWebsites() as $website) {
			$storeId = $website->getDefaultGroup()->getDefaultStoreId();
			$siteId = Mage::helper('eems_display/config')->getSiteId($storeId);
			if (empty($siteId)) {
				continue;
			}
			$outputFileName = $exportDirName . DS . $siteId . '-' . $storeId . '.csv';
			$fh = fopen($outputFileName, 'w');
			if ($fh == FALSE) {
				// @TODO Some error message should be emitted
			}
			$rows = $this->_getProductData($storeId);
			fputcsv($fh, $this->_getProductDataHeader(), ',');
			foreach ($rows as $row) {
				fputcsv($fh, $row, ',');
			}
			fclose($fh);
		}
	}
	/**
	 * Get the product collection for this store.
	 * @return object (collection of Mage_Sales_Model_Order)
	 */
	protected function _getProductCollection($storeId=null)
	{
		return Mage::getModel('catalog/product')
			->getCollection()
			->addStoreFilter($defaultStoreView)
			->addAttributeToSelect(array('sku', 'name', 'short_description', 'price', 'image'));
	}

	/**
	 * Return an array of header row values.
	 * @return array
	 */
	protected function _getProductDataHeader() {
		return array('Id', 'Name', 'Description', 'Price', 'Image URL', 'Page URL');
	}
	/**
	 * Compile the product data for Fetchback into
	 * an array that can be put into a CSV.
	 * @return array
	 */
	protected function _getProductData($storeId)
	{
		$data = array();
		$products = $this->_getProductCollection($storeId=null);
		foreach($products as $product) {
			$productImageUrl = '';
			try {
				// @TODO: Set to 150x150 size:
				$productImageUrl = $product->getImageUrl();
			} catch (Exception $e) {
				Mage::log('Error sizing Image URL for ' . $product->getSku(), $e->getMessage());
				/*
				@TODO: Either log something, or write a comment that we specifically are ignoring
				this exception.
				*/
			}
			$data[] = array(
				$product->getSku(),              // "Id"
				$product->getName(),             // "Name"
				$product->getShortDescription(), // "Description"
				$product->getPrice(),            // "Price"
				$productImageUrl,                // "Image URL"
				$product->getProductUrl(),       // "Page URL"
			);
		}
		return $data;
	}
}
