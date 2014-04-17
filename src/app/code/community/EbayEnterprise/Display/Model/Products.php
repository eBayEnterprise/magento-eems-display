<?php
class EbayEnterprise_Display_Model_Products extends Mage_Core_Model_Abstract
{
	const CSV_FIELD_DELIMITER = ',';
	const CSV_FIELD_ENCLOSURE = '"';
	/**
	 * Export product feeds. Get every store from every store group for every website.
	 */
	public function export()
	{
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
	protected function _createCsvFile($outputFileName, $dataRows) {
		$fh = fopen($outputFileName, 'w');
		if ($fh == false) {
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
		return Mage::getModel('catalog/product')
			->getCollection()
			->addStoreFilter($storeId);
	}
	/**
	 * Return an array of header row values.
	 * @return array
	 */
	protected function _getProductDataHeader() {
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
			$imageUrl = (string)Mage::helper('catalog/image')
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
		foreach($products as $collectedProduct) {
			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($collectedProduct->getId());
			$data[] = array(
				$product->getSku(),                          // "Id"
				$product->getName(),                         // "Name"
				$product->getShortDescription(),             // "Description"
				$product->getPrice(),                        // "Price"
				$this->_getResizedImage($product, $storeId), // "Image URL"
				$product->getProductUrl(),                   // "Page URL"
			);
		}
		return $data;
	}
}
