<?php
/**
 * There is no index action for this controller. You must use 'retrieve?id=' to retrieve
 * an existing feed. If we decide later we want to create feeds upon request via this
 * controller, just add another action.
 */
class EbayEnterprise_Display_IndexController extends Mage_Core_Controller_Front_Action 
{
	/**
	 * Provides a feed for the given store parameter
	 */
	public function retrieveAction()
	{
		$storeId = Mage::app()->getRequest()->getParam('id');
		echo '<h1> Get products for id ' . $storeId . '</h1>';
	}
}