<?php
class EbayEnterprise_Display_Model_Products extends Mage_Core_Model_Abstract
{

	const FEED_NAME = 'PRODUCT';
	const FILE_MASK = 0777; // FIXME: RESET TO 0774 BEFORE RELEASE

	/**
	 * Collate and transmit the day's products to Fetchback.
	 */
	public function transmitBatch()
	{
		Mage::log('Fetchback transmitBatch triggered.');

		$helper    = Mage::helper('fetchback');
		$dateStamp = gmdate('Ymd');
		$paths = array();
		// TODO: Make sure this doesn't send multiple batches of the
		//       same catalog of orders when there are multiple stores.
		foreach(Mage::app()->getStores(true) as $storeView) {
			if (!$helper->isEnabled($storeView)) continue;

			$storeCode = $helper->getSiteId($storeView);
			// Get data to send.
			$data = $this->_getProductData($storeView);
			if ($data) {
				// Compile that data into a CSV file.
				$path = $helper->normal_paths(
					Mage::getBaseDir('var'),
					'fetchback',
					'archive',
					// <PARTNER_ID>_<FEEDNAME>_YYYYMMDD.txt
					sprintf('%s_%d_%s.txt', $storeCode, self::FEED_NAME, $dateStamp)
				);
				@mkdir(dirname($path), self::FILE_MASK, true);
				$file = fopen($path, 'a');
				foreach($data as $csvline) fputcsv($file, $csvline);
				fclose($file);
			}
		}
		// @TODO: New spec reads that we're providing a URL rather than transferring a file.
		foreach ($paths as $path) {
			// Upload the csv file.
			try {
				$helper->ftpPutFile($path);
				rename($path, $path . '-sent');
			}
			catch (Exception $e) {
				Mage::logException($e);
			}
		}
		Mage::log('Fetchback transmitBatch complete.');
	}

	/**
	 * Compile the order data for Fetchback into
	 * an array that can be put into a CSV.
	 *
	 * @param Mage_Core_Model_Store $store
	 * @return array
	 */
	protected function _getProductData($store)
	{
		$data = array();
		$storeViewId = $store->getId();
		$products = $this->_getProductCollection($storeViewId)->load();
		foreach($products as $product) {
			$productImageUrl = '';
			try {
				$productImageUrl = $product->getImageUrl();
			} catch (Exception $e) {}
			$data[] = array(
				$product->getId(),                          // ProductId
				Mage::helper('fetchback')->getSiteId(), // StoreCode
				$product->getProductUrl(),                  // ProductURL
				$product->getName(),                        // LongTitle
				$product->getPrice(),                       // Price
				$productImageUrl                            // ProductImageURL
			);
		}
		return $data;
	}

	/**
	 * Get the product collection for this store.
	 *
	 * @param int $storeViewId
	 * @return object (collection of Mage_Sales_Model_Order)
	 */
	protected function _getProductCollection($storeViewId)
	{
		return Mage::getModel('catalog/product')
			->getCollection()
			// The id will be converted to the right level (website, store, view)
			->addStoreFilter($storeViewId)
			->addAttributeToSelect(array('id', 'name', 'price', 'image'));
	}
}