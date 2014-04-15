<?php
class EbayEnterprise_Display_Model_Products extends Mage_Core_Model_Abstract
{
	/**
	 * Compile the product data for Fetchback into
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
				// @TODO: Set to 150x150 size:
				$productImageUrl = $product->getImageUrl();
			} catch (Exception $e) {
				/*
				@TODO: Either log something, or write a comment that we specifically are ignoring 
				this exception.
				*/
			}
			$data[] = array(
				$product->getId(),                                // ProductId
				Mage::helper('eems_display/config')->getSiteId(), // StoreCode
				$product->getProductUrl(),                        // ProductURL
				$product->getName(),                              // LongTitle
				$product->getPrice(),                             // Price
				$productImageUrl                                  // ProductImageURL
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