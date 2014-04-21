<?php

class EbayEnterprise_Affiliate_Helper_Map_Order
{
	/**
	 * Get the order increment id from the order the item was created for.
	 * @param  array $params
	 * @return string
	 */
	public function getItemOrderId($params)
	{
		$item = $params['item'];
		return sprintf(
			$params['format'], $item->getOriginalIncrementId() ?: $item->getIncrementId()
		);
	}
	/**
	 * Get the updated item quantity - original quantity less any refunded
	 * or canceled
	 * @param  array $params
	 * @return int
	 */
	public function getItemQuantity($params)
	{
		$item = $params['item'];
		// field limit doesn't allow this to go above 99
		return (int) ($item->getQtyOrdered() - $item->getQtyRefunded() - $item->getQtyCanceled());
	}
	/**
	 * Get the corrected total for the row - price * corrected qty
	 * @param  array $params
	 * @return string
	 */
	public function getRowTotal($params)
	{
		$config = Mage::helper('eems_affiliate/config');
		// transaction type of Lead should always just be "0"
		if ($config->getTransactionType($params['store']) === $config::TRANSACTION_TYPE_LEAD) {
			return 0;
		}
		return sprintf(
			$params['format'],
			$params['item']->getBasePrice() * $this->getItemQuantity($params)
		);
	}
	/**
	 * Get the corrected amount of the order
	 * @param  array $params
	 * @return string
	 */
	public function getOrderAmount($params)
	{
		$config = Mage::helper('eems_affiliate/config');
		// transaction type of Lead should always just be "0"
		if ($config->getTransactionType($params['store']) === $config::TRANSACTION_TYPE_LEAD) {
			return 0;
		}
		$order = $params['item'];
		return sprintf(
			$params['format'],
			$order->getBaseSubtotal() - $order->getBaseSubtotalRefunded() - $order->getBaseSubtotalCanceled()
		);
	}
	/**
	 * Get the transaction type configured for the store the order was caputred in
	 * @param  array $params
	 * @return int
	 */
	public function getTransactionType($params)
	{
		return (int) Mage::helper('eems_affiliate/config')->getTransactionType($params['store']);
	}
	/**
	 * Get the order item increment id. For orders that are the result of an edit,
	 * get the increment id of the original order.
	 * @param  array $params
	 * @return string
	 */
	public function getOrderId($params)
	{
		$order = $params['item'];
		return sprintf(
			$params['format'], $order->getOriginalIncrementId() ?: $order->getIncrementId()
		);
	}
}